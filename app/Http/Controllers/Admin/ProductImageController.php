<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductImageController extends Controller
{
    public function __construct(private ImageService $images) {}

    public function store(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $request->validate([
            'image' => ['required', 'image', 'max:4096'],
            'is_primary' => ['boolean'],
        ]);

        $this->images->store($product, $request->file('image'), $request->boolean('is_primary'));

        return back()->with('success', 'Image uploaded.');
    }

    public function setPrimary(Product $product, ProductImage $image): RedirectResponse
    {
        $this->authorize('update', $product);

        abort_unless($image->product_id === $product->id, 404);

        $this->images->setPrimary($image);

        return back()->with('success', 'Primary image updated.');
    }

    public function destroy(Product $product, ProductImage $image): RedirectResponse
    {
        $this->authorize('update', $product);

        abort_unless($image->product_id === $product->id, 404);

        $this->images->delete($image);

        return back()->with('success', 'Image removed.');
    }
}