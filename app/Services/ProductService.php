<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductVariation;
use App\Models\ProductVariationAttributeValue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductService
{
    public function __construct(private SlugService $slugs, private LogService $logs) {}

   public function create(array $data): Product
{
    return DB::transaction(function () use ($data) {
        $data['slug'] = $this->slugs->generate($data['name'], Product::class);

        $categoryName = ! empty($data['category_id'])
            ? Category::find($data['category_id'])?->name
            : null;

        $data['sku'] = $this->generateSku($data['name'], $categoryName);

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
        $skuInputsChanged = false;

        if (isset($data['name']) && $data['name'] !== $product->name) {
            $data['slug'] = $this->slugs->generate(
                $data['name'],
                Product::class,
                $product->id
            );
            $skuInputsChanged = true;
        }

        if (
            isset($data['category_id'])
            && $data['category_id'] !== $product->category_id
        ) {
            $skuInputsChanged = true;
        }

        if ($skuInputsChanged) {
            $effectiveName = $data['name'] ?? $product->name;
            $effectiveCategoryId = $data['category_id'] ?? $product->category_id;

            $categoryName = ! empty($effectiveCategoryId)
                ? Category::find($effectiveCategoryId)?->name
                : null;

            $data['sku'] = $this->generateSku(
                $effectiveName,
                $categoryName,
                $product->id
            );
        } else {
            // Never trust client-supplied SKU
            unset($data['sku']);
        }

        $variations = $data['variations'] ?? [];
        unset($data['variations']);

        // ——— rest of your existing audit-diff logic, unchanged ———
        $oldVariations = $product->variations()
            ->get()
            ->keyBy(fn ($v) => $v->getKey())
            ->map(fn ($v) => $v->getAttributes())
            ->all();

        $product->fill($data);
        $changes = $product->getDirty();

        $fromRecords = [];
        foreach ($changes as $attribute => $newValue) {
            $fromRecords[$attribute] = $product->getOriginal($attribute);
        }

        $product->save();

        $toRecords = [];
        foreach ($product->getChanges() as $attribute => $newValue) {
            $toRecords[$attribute] = $newValue;
        }

        $this->syncVariations($product, $variations);

        $newVariations = $product->variations()
            ->get()
            ->keyBy(fn ($v) => $v->getKey())
            ->map(fn ($v) => $v->getAttributes())
            ->all();

        $variationFromRecords = [];
        $variationToRecords = [];

        $variationIds = array_unique(array_merge(
            array_keys($oldVariations),
            array_keys($newVariations)
        ));

        foreach ($variationIds as $variationId) {
            $old = $oldVariations[$variationId] ?? null;
            $new = $newVariations[$variationId] ?? null;

            if ($old === null) {
                $variationFromRecords[$variationId] = null;
                $variationToRecords[$variationId] = array_diff_key(
                    $new,
                    array_flip(['created_at', 'updated_at'])
                );
                continue;
            }

            if ($new === null) {
                $variationFromRecords[$variationId] = array_diff_key(
                    $old,
                    array_flip(['created_at', 'updated_at'])
                );
                $variationToRecords[$variationId] = null;
                continue;
            }

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

        if (! empty($variationFromRecords)) {
            $fromRecords['variations'] = $variationFromRecords;
            $toRecords['variations'] = $variationToRecords;
        }

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

    // If the product SKU just changed, every variation's SKU is stale.
    $productSkuChanged = $product->wasChanged('sku');

    foreach ($variations as $row) {
        $attributeValueIds = $row['attribute_value_ids'] ?? [];
        unset($row['attribute_value_ids']);

        // Never trust client-supplied variation SKU
        unset($row['sku']);

        if (! empty($row['id']) && in_array($row['id'], $existingIds, true)) {
            $variation = ProductVariation::find($row['id']);

            $attributesChanged = $this->variationAttributesChanged(
                $variation,
                $attributeValueIds
            );

            if ($attributesChanged || $productSkuChanged) {
                $labels = ProductAttributeValue::whereIn('id', $attributeValueIds)
                    ->pluck('value')
                    ->all();

                $row['sku'] = $this->generateVariationSku(
                    $product->sku,
                    $labels,
                    $variation->id
                );
            }

            $variation->update($row);
        } else {
            $labels = ProductAttributeValue::whereIn('id', $attributeValueIds)
                ->pluck('value')
                ->all();

            $row['sku'] = $this->generateVariationSku(
                $product->sku,
                $labels
            );
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

/**
 * True when the variation's stored attribute values differ from $newValueIds.
 */
private function variationAttributesChanged(
    ProductVariation $variation,
    array $newValueIds
): bool {
    $current = ProductVariationAttributeValue::where('variation_id', $variation->id)
        ->pluck('attribute_value_id')
        ->all();

    sort($current);

    $next = array_values(array_unique(array_filter($newValueIds)));
    sort($next);

    return $current !== $next;
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

    protected function generateSku(
    string $name,
    ?string $categoryName = null,
    ?string $ignoreId = null
): string {
    $prefix = $this->buildSkuPrefix($name);

    if ($categoryName) {
        $prefix = $this->buildSkuPrefix($categoryName) . '-' . $prefix;
    }

    return $this->ensureUniqueSku($prefix, 'products', $ignoreId);
}

protected function generateVariationSku(
    string $productSku,
    array $attributeValues,
    ?string $ignoreId = null
): string {
    $suffix = collect($attributeValues)
        ->map(fn ($v) => strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $v)))
        ->map(fn ($v) => substr($v, 0, 3))
        ->implode('-');

    $base = $suffix ? "{$productSku}-{$suffix}" : "{$productSku}-VAR";

    return $this->ensureUniqueSku($base, 'product_variations', $ignoreId);
}

protected function ensureUniqueSku(
    string $base,
    string $table,
    ?string $ignoreId = null,
    string $column = 'sku'
): string {
    $candidate = $base;
    $i = 1;

    while (
        DB::table($table)
            ->where($column, $candidate)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
    ) {
        $candidate = $base . '-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT);
        $i++;
    }

    return $candidate;
}

    protected function buildSkuPrefix(string $text, int $length = 4): string
    {
        $clean = preg_replace('/[^A-Za-z0-9\s]/', '', $text);
        $words = preg_split('/\s+/', trim($clean));

        // Take first letters of up to 4 words, or first 4 chars of single word
        if (count($words) === 1) {
            return strtoupper(substr($words[0], 0, $length));
        }

        return strtoupper(implode('', array_map(
            fn ($w) => substr($w, 0, 1),
            array_slice($words, 0, $length)
        )));
    }

    
}
