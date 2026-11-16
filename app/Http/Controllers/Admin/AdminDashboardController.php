<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $lowStockThreshold = 5;

        $stats = [
            'total_products' => Product::count(),
            'active_products' => Product::where('is_active', true)->count(),
            'total_variations' => ProductVariation::count(),
            'total_customers' => User::where('role', 'customer')->count(),
            'low_stock' => ProductVariation::query()
                ->where('stock_quantity', '>', 0)
                ->whereRaw("stock_quantity <= COALESCE(low_stock_threshold, {$lowStockThreshold})")
                ->count(),
            'out_of_stock' => ProductVariation::where('stock_quantity', '<=', 0)->count(),
        ];

        $lowStockList = ProductVariation::query()
            ->with(['product:id,name,sku'])
            ->where('stock_quantity', '>', 0)
            ->whereRaw("stock_quantity <= COALESCE(low_stock_threshold, {$lowStockThreshold})")
            ->orderBy('stock_quantity')
            ->limit(10)
            ->get()
            ->map(fn (ProductVariation $v) => [
                'id' => $v->id,
                'sku' => $v->sku,
                'stock_quantity' => $v->stock_quantity,
                'low_stock_threshold' => $v->low_stock_threshold ?? $lowStockThreshold,
                'product' => [
                    'id' => $v->product->id,
                    'name' => $v->product->name,
                ],
            ]);

        $outOfStockList = ProductVariation::query()
            ->with(['product:id,name,sku'])
            ->where('stock_quantity', '<=', 0)
            ->orderBy('sku')
            ->limit(10)
            ->get()
            ->map(fn (ProductVariation $v) => [
                'id' => $v->id,
                'sku' => $v->sku,
                'product' => [
                    'id' => $v->product->id,
                    'name' => $v->product->name,
                ],
            ]);

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'lowStockList' => $lowStockList,
            'outOfStockList' => $outOfStockList,
        ]);
    }
}