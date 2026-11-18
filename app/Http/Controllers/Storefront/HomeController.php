<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\StoreSetting;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $featuredLimit = (int) StoreSetting::get('featured_products_limit', 8);
        $newArrivalsLimit = (int) StoreSetting::get('new_arrivals_limit', 8);

        return Inertia::render('Welcome', [
            'banners' => Banner::active()
                ->get()
                ->map(fn ($b) => [
                    'id' => $b->id,
                    'title' => $b->title,
                    'subtitle' => $b->subtitle,
                    'image_url' => $b->image_url,
                    'link_url' => $b->link_url,
                    'link_text' => $b->link_text,
                ])
                ->values(),

            'featured' => Product::active()
                ->where('is_featured', true)
                ->with([
                    'primaryImage',
                    'defaultVariation',
                ])
                ->limit($featuredLimit)
                ->get()
                ->map(fn (Product $p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'price' => number_format(
                        (float) (
                            $p->defaultVariation?->sale_price
                            ?? $p->defaultVariation?->price
                            ?? 0
                        ),
                        0
                    ),
                    'primary_image_url' => $p->primary_image_url,
                ])
                ->values(),

            'newArrivals' => Product::active()
                ->latest()
                ->with([
                    'primaryImage',
                    'defaultVariation',
                ])
                ->limit($newArrivalsLimit)
                ->get()
                ->map(fn (Product $p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'price' => number_format(
                        (float) (
                            $p->defaultVariation?->sale_price
                            ?? $p->defaultVariation?->price
                            ?? 0
                        ),
                        0
                    ),
                    'primary_image_url' => $p->primary_image_url,
                ])
                ->values(),

            'categories' => Category::active()
                ->roots()
                ->with([
                    'children' => fn ($q) => $q
                        ->active()
                        ->orderBy('sort_order'),
                ])
                ->orderBy('sort_order')
                ->get()
                ->map(fn (Category $c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                    'image_url' => $c->image_url,
                    'children' => $c->children
                        ->map(fn (Category $ch) => [
                            'id' => $ch->id,
                            'name' => $ch->name,
                            'slug' => $ch->slug,
                            'image_url' => $ch->image_url,
                        ])
                        ->values(),
                ])
                ->values(),
        ]);
    }
}
