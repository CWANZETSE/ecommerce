<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShopController extends Controller
{
    public function index(Request $request): Response
    {
        // --- Normalize multi-select categories ---
        $categories = $request->input('categories', []);
        if (!is_array($categories)) {
            $categories = [$categories];
        }
        $categories = array_values(array_filter(
            $categories,
            fn ($c) => is_string($c) && $c !== ''
        ));

        $products = Product::query()
            ->active()
            ->with(['primaryImage', 'category:id,name,slug', 'brand:id,name,slug'])
            ->when($categories, function ($q) use ($categories) {
                // Match products whose category slug OR whose parent's slug is in the selected list
                $q->whereHas('category', function ($q) use ($categories) {
                    $q->whereIn('slug', $categories)
                      ->orWhereHas('parent', fn ($p) => $p->whereIn('slug', $categories));
                });
            })
            ->when($request->filled('brand'), fn ($q) => $q->whereHas('brand', fn ($b) => $b->where('slug', $request->brand)))
            ->when($request->filled('min_price'), fn ($q) => $q->where('price', '>=', $request->min_price))
            ->when($request->filled('max_price'), fn ($q) => $q->where('price', '<=', $request->max_price))
            ->when($request->filled('search'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('sku', 'like', "%{$request->search}%")
                  ->orWhere('short_description', 'like', "%{$request->search}%");
            }))
            ->when($request->boolean('in_stock'), fn ($q) => $q->inStock())
            ->when(true, function ($q) use ($request) {
                match ($request->get('sort', 'newest')) {
                    'price_asc' => $q->orderBy('price'),
                    'price_desc' => $q->orderByDesc('price'),
                    'name_asc' => $q->orderBy('name'),
                    'featured' => $q->orderByDesc('is_featured'),
                    default => $q->latest(),
                };
            })
            ->paginate(2)
            ->withQueryString();

        return Inertia::render('Storefront/Products/Index', [
            'products'   => $products,
            'categories' => Category::active()->roots()->with(['children' => fn ($q) => $q->active()])->get(),
            'brands'     => Brand::active()->orderBy('name')->get(['id', 'name', 'slug']),
            'filters'    => [
                'search'     => $request->input('search'),
                'sort'       => $request->input('sort', 'newest'),
                'categories' => $categories,        // ← array, always
                'brand'      => $request->input('brand'),
                'min_price'  => $request->input('min_price'),
                'max_price'  => $request->input('max_price'),
                'in_stock'   => $request->boolean('in_stock'),
            ],
        ]);
    }
}