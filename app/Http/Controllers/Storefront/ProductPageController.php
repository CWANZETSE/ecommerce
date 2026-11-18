<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReview;
use Inertia\Inertia;
use Inertia\Response;

class ProductPageController extends Controller
{
    public function show(string $slug): Response
    {
        $product = Product::query()
            ->active()
            ->where('slug', $slug)
            ->with([
                'images',
                'category:id,name,slug',
                'brand:id,name,slug',
                'defaultVariation',
                'variations.attributeValues.attribute',
            ])
            ->firstOrFail();

        $defaultVariation = $product->defaultVariation;

        // Initial product pricing comes strictly from its default variation.
        $productPrice = (float) ($defaultVariation?->price ?? 0);

        $productSale = $defaultVariation?->sale_price !== null
            ? (float) $defaultVariation->sale_price
            : null;

        $productCurrent = $productSale ?? $productPrice;

        $productHasSale = $productSale !== null
            && $productSale < $productPrice;

        $productDiscountPct = $productHasSale && $productPrice > 0
            ? (int) round((1 - $productSale / $productPrice) * 100)
            : 0;

        $related = Product::query()
            ->active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with([
                'primaryImage',
                'defaultVariation',
            ])
            ->inRandomOrder()
            ->limit(4)
            ->get()
            ->map(function (Product $p) {
                $variation = $p->defaultVariation;

                $price = (float) ($variation?->price ?? 0);

                $salePrice = $variation?->sale_price !== null
                    ? (float) $variation->sale_price
                    : null;

                $currentPrice = $salePrice ?? $price;

                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'price' => $currentPrice,
                    'price_formatted' => number_format($currentPrice, 0),
                    'primary_image_url' => $p->primary_image_url,
                ];
            })
            ->values();

        $reviews = ProductReview::where('product_id', $product->id)
            ->where('status', 'approved')
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->get();

        $averageRating = $reviews->avg('rating');

        return Inertia::render('Storefront/Products/Show', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'sku' => $product->sku,

                // Initial product pricing: default variation only.
                'price' => $productPrice,
                'sale_price' => $productSale,

                // Precomputed display values.
                'current_price' => (float) $productCurrent,
                'current_price_formatted' => number_format($productCurrent, 0),
                'original_price' => $productHasSale ? $productPrice : null,
                'original_price_formatted' => $productHasSale
                    ? number_format($productPrice, 0)
                    : null,
                'discount_percent' => $productDiscountPct,
                'has_sale' => $productHasSale,

                'short_description' => $product->short_description,
                'description' => $product->description,

                'images' => $product->images->map(fn ($img) => [
                    'id' => $img->id,
                    'path' => $img->path,
                    'url' => $img->image_url,
                    'is_primary' => $img->is_primary,
                ])->values(),

                'category' => $product->category ? [
                    'name' => $product->category->name,
                    'slug' => $product->category->slug,
                ] : null,

                'brand' => $product->brand ? [
                    'name' => $product->brand->name,
                ] : null,

                'variations' => $product->variations->map(function ($v) {
                    $vPrice = (float) $v->price;

                    $vSale = $v->sale_price !== null
                        ? (float) $v->sale_price
                        : null;

                    // Effective price comes strictly from this variation.
                    $current = $vSale ?? $vPrice;

                    // Show original price only for an actual variation discount.
                    $hasSale = $vSale !== null && $vSale < $vPrice;

                    $original = $hasSale ? $vPrice : null;

                    $discount = $hasSale && $original > 0
                        ? (int) round((1 - $current / $original) * 100)
                        : 0;

                    return [
                        'id' => $v->id,
                        'sku' => $v->sku,
                        'price' => $vPrice,
                        'sale_price' => $vSale,
                        'current_price' => (float) $current,
                        'current_price_formatted' => number_format($current, 0),
                        'original_price' => $original,
                        'original_price_formatted' => $original !== null
                            ? number_format($original, 0)
                            : null,
                        'discount_percent' => $discount,
                        'stock_quantity' => (int) $v->stock_quantity,

                        'attribute_values' => $v->attributeValues->map(fn ($av) => [
                            'id' => $av->id,
                            'value' => $av->value,
                            'attribute' => [
                                'id' => $av->attribute->id,
                                'name' => $av->attribute->name,
                            ],
                        ])->values(),
                    ];
                })->values(),
            ],

            'related' => $related,

            'reviews' => $reviews->map(fn ($r) => [
                'id' => $r->id,
                'rating' => $r->rating,
                'title' => $r->title,
                'body' => $r->body,
                'user_name' => $r->user?->name ?? 'Anonymous',
                'verified' => $r->is_verified_purchase,
                'created_at' => $r->created_at->toDateString(),
            ])->values(),

            'average_rating' => round((float) ($averageRating ?? 0), 1),
            'review_count' => $reviews->count(),
        ]);
    }
}
