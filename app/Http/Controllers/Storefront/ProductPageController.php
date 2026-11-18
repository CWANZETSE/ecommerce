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
                'variations.attributeValues.attribute',
            ])
            ->firstOrFail();

        $related = Product::query()
            ->active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with('primaryImage')
            ->inRandomOrder()
            ->limit(4)
            ->get()
            ->map(function (Product $p) {
                $effective = $p->sale_price ?? $p->price;

                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'price' => $p->sale_price ?? $p->price,
                    'price_formatted' => number_format((float) $p->sale_price ?? $p->price, 0),
                    'primary_image_url' => $p->primary_image_url,
                ];
            });

        $reviews = ProductReview::where('product_id', $product->id)
            ->where('status', 'approved')
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->get();

        $averageRating = $reviews->avg('rating');

        /* -------- Precompute product-level pricing -------- */
        $productPrice = (float) $product->price;
        $productSale = $product->sale_price !== null ? (float) $product->sale_price : null;
        $productCurrent = $productSale ?? $productPrice;
        $productHasSale = $productSale !== null && $productSale < $productPrice;
        $productDiscountPct = $productHasSale
            ? (int) round((1 - $productSale / $productPrice) * 100)
            : 0;

        return Inertia::render('Storefront/Products/Show', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'sku' => $product->sku,

                /* Raw numerics — safe for JS math */
                'price' => $productPrice,
                'sale_price' => $productSale,

                /* Precomputed display values */
                'current_price' => (float) $productCurrent,
                'current_price_formatted' => number_format($productCurrent, 0),
                'original_price' => $productHasSale ? $productPrice : null,
                'original_price_formatted' => $productHasSale ? number_format($productPrice, 0) : null,
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
                'brand' => $product->brand ? ['name' => $product->brand->name] : null,

                'variations' => $product->variations->map(function ($v) use ($productPrice, $productSale) {
                    $vPrice = $v->price !== null ? (float) $v->price : null;
                    $vSale = $v->sale_price !== null ? (float) $v->sale_price : null;

                    // Effective variation price: sale > base price > product fallback
                    $current = $vSale ?? $vPrice ?? $productSale ?? $productPrice;

                    // Original (strikethrough) only when there's a real discount
                    if ($vSale !== null && $vPrice !== null && $vSale < $vPrice) {
                        $original = $vPrice;
                    } elseif ($vSale !== null && $productSale === null && $productPrice > $vSale) {
                        $original = $productPrice;
                    } elseif ($vPrice !== null && $productSale !== null && $vPrice < $productSale) {
                        $original = $productSale;
                    } else {
                        $original = null;
                    }

                    $discount = ($original !== null && $original > 0)
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
                        'original_price_formatted' => $original !== null ? number_format($original, 0) : null,
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
