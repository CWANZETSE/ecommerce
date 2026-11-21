<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StockMovementType;
use App\Http\Controllers\Controller;
use App\Models\ProductVariation;
use App\Models\StockMovement;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    public function __construct(private InventoryService $inventory) {}

       public function index(Request $request): Response
    {
        $filter = $request->get('filter', 'all'); // all | low | out

        $query = ProductVariation::query()
            ->with(['product:id,name,sku', 'attributeValues.attribute:id,name'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('sku', 'like', "%{$request->search}%")
                        ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$request->search}%"));
                });
            })
            ->when($filter === 'out', fn ($q) => $q->where('stock_quantity', '<=', 0))
            ->when($filter === 'low', fn ($q) => $q
                ->where('stock_quantity', '>', 0)
                ->whereRaw('stock_quantity <= COALESCE(low_stock_threshold, 5)'))
            ->orderBy('stock_quantity')
            ->orderBy('sku');

        $variations = $query->paginate(10)->withQueryString();

        return Inertia::render('Admin/Inventory/Index', [
            'variations' => $variations,
            'filters' => [
                'filter' => $filter,
                'search' => $request->get('search'),
            ],
            'counts' => [
                'all' => ProductVariation::count(),
                'low' => ProductVariation::query()
                    ->where('stock_quantity', '>', 0)
                    ->whereRaw('stock_quantity <= COALESCE(low_stock_threshold, 5)')
                    ->count(),
                'out' => ProductVariation::where('stock_quantity', '<=', 0)->count(),
            ],
        ]);
    }

    public function movements(ProductVariation $variation): Response
{
    $movements = StockMovement::where('variation_id', $variation->id)
        ->with(['user:id,name'])
        ->orderByDesc('created_at')
        ->paginate(30);

    $movements->getCollection()->transform(function ($movement) {
        $movement->created_at_formatted = $movement->created_at
            ? \Carbon\Carbon::parse($movement->created_at)->format('D d-m-Y H:i')
            : null;

        return $movement;
    });

    return Inertia::render('Admin/Inventory/Movements', [
        'variation' => $variation->load([
            'product:id,name,sku',
            'attributeValues.attribute:id,name',
        ]),
        'movements' => $movements,
    ]);
}

    public function adjust(Request $request, ProductVariation $variation): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:receive,damage,set'],
            'quantity' => ['required', 'integer', 'min:0'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $qty = (int) $data['quantity'];

        try {
            if ($data['type'] === 'set') {
                $delta = $qty - $variation->stock_quantity;
                if ($delta === 0) {
                    return back()->with('error', 'Stock level unchanged.');
                }
                $this->inventory->adjust(
                    $variation,
                    $delta,
                    StockMovementType::Adjust,
                    $data['reason'] ?? 'Manual adjustment',
                );
            } elseif ($data['type'] === 'receive') {
                if ($qty < 1) {
                    return back()->with('error', 'Quantity must be at least 1.');
                }
                $this->inventory->receive($variation, $qty, $data['reason'] ?? null);
            } else { // damage
                if ($qty < 1) {
                    return back()->with('error', 'Quantity must be at least 1.');
                }
                $this->inventory->recordDamage($variation, $qty, $data['reason'] ?? 'Damaged / lost');
            }
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Stock updated successfully.');
    }
}
