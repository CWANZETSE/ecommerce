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
    public function __construct(private SlugService $slugs,private LogService $logs) {}

    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $data['slug'] = $this->slugs->generate($data['name'], Product::class);
            $variations = $data['variations'] ?? [];
            unset($data['variations']);

            $product = Product::create($data);
            $this->syncVariations($product, $variations);

            $this->logs->log(
                'Created',
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
            $data['slug'] = $this->slugs->generate(
                $data['name'],
                Product::class,
                $product->id
            );
        }

        $variations = $data['variations'] ?? [];
        unset($data['variations']);

        // 1. Capture existing variation records before synchronization
        $oldVariations = $product->variations()
            ->get()
            ->keyBy(fn ($variation) => $variation->getKey())
            ->map(fn ($variation) => $variation->getAttributes())
            ->all();

        // 2. Capture original product values
        $product->fill($data);

        $changes = $product->getDirty();

        $fromRecords = [];

        foreach ($changes as $attribute => $newValue) {
            $fromRecords[$attribute] = $product->getOriginal($attribute);
        }

        // 3. Update product
        $product->save();

        $toRecords = [];

        foreach ($product->getChanges() as $attribute => $newValue) {
            $toRecords[$attribute] = $newValue;
        }

        // 4. Synchronize variations
        $this->syncVariations($product, $variations);

        // 5. Capture variation records after synchronization
        $newVariations = $product->variations()
            ->get()
            ->keyBy(fn ($variation) => $variation->getKey())
            ->map(fn ($variation) => $variation->getAttributes())
            ->all();

        // 6. Compare variation records and capture only changed attributes
        $variationFromRecords = [];
        $variationToRecords = [];

        $variationIds = array_unique(array_merge(
            array_keys($oldVariations),
            array_keys($newVariations)
        ));

        foreach ($variationIds as $variationId) {
            $old = $oldVariations[$variationId] ?? null;
            $new = $newVariations[$variationId] ?? null;

            // Newly created variation
            if ($old === null) {
                $variationFromRecords[$variationId] = null;

                $variationToRecords[$variationId] = array_diff_key(
                    $new,
                    array_flip(['created_at', 'updated_at'])
                );

                continue;
            }

            // Deleted variation
            if ($new === null) {
                $variationFromRecords[$variationId] = array_diff_key(
                    $old,
                    array_flip(['created_at', 'updated_at'])
                );

                $variationToRecords[$variationId] = null;

                continue;
            }

            // Existing variation: capture only modified attributes
            foreach ($new as $attribute => $newValue) {
                if (in_array($attribute, ['created_at', 'updated_at'], true)) {
                    continue;
                }

                $oldValue = $old[$attribute] ?? null;

                if ($oldValue !== $newValue) {
                    $variationFromRecords[$variationId][$attribute] = $oldValue;
                    $variationToRecords[$variationId][$attribute] = $newValue;
                }
            }
        }

        // 7. Include variation changes in the product audit log
        if (! empty($variationFromRecords)) {
            $fromRecords['variations'] = $variationFromRecords;
            $toRecords['variations'] = $variationToRecords;
        }

        // 8. Log the complete product update
        $this->logs->log(
            'Updated',
            $product,
            [
                'name' => $product->name,
                'sku' => $product->sku,
                'variation_count' => count($variations),
            ],
            "Updated product \"{$product->name}\" ({$product->sku})",
            $fromRecords ?: null,
            $toRecords ?: null
        );

        return $product->fresh(['variations.attributeValues', 'images']);
    });
}

    public function delete(Product $product): void
    {
        DB::transaction(function () use ($product) {
            $this->logs->log(
                'Deleted',
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

}
