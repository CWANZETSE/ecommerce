<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    public function store(Product $product, UploadedFile $file, bool $makePrimary = false): ProductImage
    {
        $path = $file->store("products/{$product->id}", 'public');

        $hasPrimary = $product->images()->where('is_primary', true)->exists();

        $image = ProductImage::create([
            'product_id' => $product->id,
            'path' => $path,
            'alt' => $file->getClientOriginalName(),
            'sort_order' => $product->images()->count(),
            'is_primary' => $makePrimary || ! $hasPrimary,
        ]);

        if ($image->is_primary) {
            $this->clearOtherPrimaries($product, $image->id);
        }

        return $image;
    }

    public function setPrimary(ProductImage $image): void
    {
        $this->clearOtherPrimaries($image->product, $image->id);
        $image->update(['is_primary' => true]);
    }

    public function delete(ProductImage $image): void
    {
        Storage::disk('public')->delete($image->path);

        $wasPrimary = $image->is_primary;
        $product = $image->product;
        $image->delete();

        if ($wasPrimary) {
            $next = $product->images()->first();
            $next?->update(['is_primary' => true]);
        }
    }

    private function clearOtherPrimaries(Product $product, string $keepId): void
    {
        $product->images()
            ->where('id', '!=', $keepId)
            ->where('is_primary', true)
            ->update(['is_primary' => false]);
    }
}