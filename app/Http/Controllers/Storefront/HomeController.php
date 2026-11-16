<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Welcome', [
            'banners' => Banner::active()->get()->map(fn ($b) => [
                'id' => $b->id,
                'title' => $b->title,
                'subtitle' => $b->subtitle,
                'image_url' => $b->image_url,
                'link_url' => $b->link_url,
                'link_text' => $b->link_text,
            ])->values(),
            'featured' => Product::active()
                ->where('is_featured', true)
                ->with('primaryImage')
                ->limit(8)
                ->get()
                ->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'price' => number_format($p->sale_price ?? $p->price),
                    'primary_image' => $p->primaryImage ? ['path' => $p->primaryImage->path] : null,
                ]),
            'newArrivals' => Product::active()
                ->latest()
                ->with('primaryImage')
                ->limit(8)
                ->get()
                ->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'price' => number_format($p->sale_price ?? $p->price),
                    'primary_image' => $p->primaryImage ? ['path' => $p->primaryImage->path] : null,
                ]),
            'categories' => Category::active()->roots()
                ->with(['children' => fn ($q) => $q->active()])
                ->orderBy('sort_order')
                ->get(),
        ]);
    }
}