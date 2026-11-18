<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShopController extends Controller
{
    public function index(Request $request): Response
    {
        // Normalize multi-select categories.
        $categories = $request->input('categories', []);

        if (! is_array($categories)) {
            $categories = [$categories];
        }

        $categories = array_values(array_filter(
            $categories,
            fn ($category) => is_string($category) && $category !== ''
        ));

        $minPrice = $request->filled('min_price')
            ? (float) $request->input('min_price')
            : null;

        $maxPrice = $request->filled('max_price')
            ? (float) $request->input('max_price')
            : null;

        $search = trim((string) $request->input('search', ''));

        $products = Product::query()
            ->active()
            ->with([
                'primaryImage',
                'category:id,name,slug',
                'brand:id,name,slug',
                'defaultVariation:id,product_id,price,sale_price,is_default',
            ])
            ->withExists('variations')
            ->when($categories, function (Builder $query) use ($categories) {
                $query->whereHas('category', function (Builder $categoryQuery) use ($categories) {
                    $categoryQuery
                        ->whereIn('slug', $categories)
                        ->orWhereHas('parent', function (Builder $parentQuery) use ($categories) {
                            $parentQuery->whereIn('slug', $categories);
                        });
                });
            })
            ->when($request->filled('brand'), function (Builder $query) use ($request) {
                $query->whereHas('brand', function (Builder $brandQuery) use ($request) {
                    $brandQuery->where('slug', $request->input('brand'));
                });
            })
            // Filter using the default variation's effective price.
            ->when($minPrice !== null, function (Builder $query) use ($minPrice) {
                $query->whereHas('defaultVariation', function (Builder $variationQuery) use ($minPrice) {
                    $variationQuery->whereRaw(
                        'COALESCE(sale_price, price) >= ?',
                        [$minPrice]
                    );
                });
            })
            ->when($maxPrice !== null, function (Builder $query) use ($maxPrice) {
                $query->whereHas('defaultVariation', function (Builder $variationQuery) use ($maxPrice) {
                    $variationQuery->whereRaw(
                        'COALESCE(sale_price, price) <= ?',
                        [$maxPrice]
                    );
                });
            })
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $searchQuery) use ($search) {
                    $searchQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('short_description', 'like', "%{$search}%");
                });
            })
            ->when($request->boolean('in_stock'), function (Builder $query) {
                $query->inStock();
            })
            ->when(true, function (Builder $query) use ($request) {
                match ($request->input('sort', 'newest')) {
                    'price_asc' => $query->orderBy(
                        Product::query()
                            ->selectRaw('COALESCE(product_variations.sale_price, product_variations.price)')
                            ->from('product_variations')
                            ->whereColumn('product_variations.product_id', 'products.id')
                            ->where('product_variations.is_default', true)
                            ->limit(1)
                    ),

                    'price_desc' => $query->orderByDesc(
                        Product::query()
                            ->selectRaw('COALESCE(product_variations.sale_price, product_variations.price)')
                            ->from('product_variations')
                            ->whereColumn('product_variations.product_id', 'products.id')
                            ->where('product_variations.is_default', true)
                            ->limit(1)
                    ),

                    'name_asc' => $query->orderBy('name'),

                    'featured' => $query->orderByDesc('is_featured'),

                    default => $query->latest(),
                };
            })
            ->paginate(10)
            ->withQueryString()
            ->through(function (Product $product) {
                $defaultVariation = $product->defaultVariation;

                $basePrice = $defaultVariation?->price;
                $salePrice = $defaultVariation?->sale_price;

                // Only treat a lower sale price as an actual discount.
                $hasSale = $salePrice !== null
                    && $basePrice !== null
                    && (float) $salePrice < (float) $basePrice;

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,

                    // Prices are derived exclusively from the default variation.
                    'price' => $basePrice !== null
                        ? (float) $basePrice
                        : 0,

                    'sale_price' => $hasSale
                        ? (float) $salePrice
                        : null,

                    'stock_quantity' => (int) $product->total_stock,

                    'type' => $product->variations_exists
                        ? 'variable'
                        : 'simple',

                    'primary_image_url' => $product->primary_image_url,

                    'category' => $product->category ? [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                    ] : null,
                ];
            });

        return Inertia::render('Storefront/Products/Index', [
            'products' => $products,

            'categories' => Category::query()
    ->active()
    ->roots()
    ->with([
        'children' => fn ($query) => $query
            ->active()
            ->orderBy('name'),
    ])
    ->orderBy('name')
    ->get(),

            'brands' => Brand::query()
                ->active()
                ->orderBy('name')
                ->get(['id', 'name', 'slug']),

            'filters' => [
                'search' => $request->input('search'),
                'sort' => $request->input('sort', 'newest'),
                'categories' => $categories,
                'brand' => $request->input('brand'),
                'min_price' => $request->input('min_price'),
                'max_price' => $request->input('max_price'),
                'in_stock' => $request->boolean('in_stock'),
            ],
        ]);
    }
}
