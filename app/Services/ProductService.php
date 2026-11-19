<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductVariation;
use App\Models\ProductVariationAttributeValue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductService
{
    public function __construct(private SlugService $slugs) {}

    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $data['slug'] = $this->slugs->generate($data['name'], Product::class);
            $variations = $data['variations'] ?? [];
            unset($data['variations']);

            $product = Product::create($data);
            $this->syncVariations($product, $variations);

            $this->log(
                'product.created',
                $product,
                [
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'variation_count' => count($variations),
                ],
                "Created product \"{$product->name}\" ({$product->sku})"
            );

            return $product;
        });
    }

    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            if (isset($data['name']) && $data['name'] !== $product->name) {
                $data['slug'] = $this->slugs->generate($data['name'], Product::class, $product->id);
            }

            $variations = $data['variations'] ?? [];
            unset($data['variations']);

            $product->update($data);
            $this->syncVariations($product, $variations);

            // update
            $this->log(
                'product.updated',
                $product,
                [
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'variation_count' => count($variations),
                ],
                "Updated product \"{$product->name}\" ({$product->sku})"
            );

            return $product->fresh(['variations.attributeValues', 'images']);
        });
    }

    public function delete(Product $product): void
    {
        DB::transaction(function () use ($product) {
            $this->log(
                'product.deleted',
                $product,
                ['name' => $product->name],
                "Deleted product \"{$product->name}\""
            );
            $product->delete();
        });
    }

    private function syncVariations(Product $product, array $variations): void
    {
        $existingIds = $product->variations()->pluck('id')->toArray();
        $keptIds = [];

        foreach ($variations as $row) {
            $attributeValueIds = $row['attribute_value_ids'] ?? [];
            unset($row['attribute_value_ids']);

            if (! empty($row['id']) && in_array($row['id'], $existingIds, true)) {
                $variation = ProductVariation::find($row['id']);
                $variation->update($row);
            } else {
                if (empty($row['sku'])) {
                    $row['sku'] = strtoupper($product->sku.'-'.Str::random(4));
                }
                $row['product_id'] = $product->id;
                $variation = ProductVariation::create($row);
            }

            $this->syncVariationAttributes($variation, $attributeValueIds);
            $keptIds[] = $variation->id;
        }

        $toDelete = array_diff($existingIds, $keptIds);
        if (! empty($toDelete)) {
            ProductVariation::whereIn('id', $toDelete)->delete();
        }
    }

    private function syncVariationAttributes(ProductVariation $variation, array $attributeValueIds): void
    {
        $attributeValueIds = array_values(array_unique(array_filter($attributeValueIds)));

        $values = ProductAttributeValue::whereIn('id', $attributeValueIds)
            ->get(['id', 'attribute_id']);

        $wantedByAttribute = [];
        foreach ($values as $value) {
            $wantedByAttribute[$value->attribute_id] = $value->id;
        }

        ProductVariationAttributeValue::where('variation_id', $variation->id)
            ->whereNotIn('attribute_id', array_keys($wantedByAttribute))
            ->delete();

        foreach ($wantedByAttribute as $attributeId => $valueId) {
            ProductVariationAttributeValue::updateOrCreate(
                [
                    'variation_id' => $variation->id,
                    'attribute_id' => $attributeId,
                ],
                [
                    'id' => (string) Str::uuid(),
                    'attribute_value_id' => $valueId,
                ]
            );
        }
    }

    private function log(string $action, Product $subject, array $props = [], ?string $description = null): void
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'subject_type' => Product::class,
            'subject_id' => $subject->id,
            'properties' => $props,
            'ip' => request()->ip(),
        ]);
    }
}
