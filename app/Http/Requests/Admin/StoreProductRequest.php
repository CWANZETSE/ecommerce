<?php

namespace App\Http\Requests\Admin;

use App\Models\ProductAttributeValue;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku'],
            'category_id' => ['required', 'uuid', Rule::exists('categories', 'id')],
            'brand_id' => ['nullable', 'uuid', Rule::exists('brands', 'id')],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],

            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:4096'],

            'variations' => ['required', 'array', 'min:1'],
            'variations.*.sku' => ['required', 'string', 'max:100', 'distinct', 'unique:product_variations,sku'],
            'variations.*.price' => ['required', 'numeric', 'min:0'],
            'variations.*.sale_price' => ['nullable', 'numeric', 'min:0'],
            'variations.*.stock_quantity' => ['required', 'integer', 'min:0'],
            'variations.*.low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'variations.*.attribute_value_ids' => ['required', 'array', 'min:1'],
            'variations.*.attribute_value_ids.*' => ['uuid', Rule::exists('product_attribute_values', 'id')],
            'variations.*.is_default' => [
                'required',
                'boolean',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $rows = (array) $this->input('variations', []);
            $this->validateAttributeCombinations($v, $rows);
        });
    }

    /**
     * Ensure each variation has at most one value per attribute,
     * and no two variations share an identical attribute combination.
     */
    protected function validateAttributeCombinations(Validator $v, array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        $allValueIds = collect($rows)
            ->flatMap(fn ($r) => $r['attribute_value_ids'] ?? [])
            ->filter()
            ->unique()
            ->values()
            ->all();

        $attributeByValue = ProductAttributeValue::whereIn('id', $allValueIds)
            ->pluck('attribute_id', 'id')
            ->all();

        $seen = [];
        foreach ($rows as $i => $row) {
            $valueIds = array_values(array_unique($row['attribute_value_ids'] ?? []));
            if (empty($valueIds)) {
                continue;
            }

            // one value per attribute
            $byAttribute = [];
            foreach ($valueIds as $vid) {
                $attrId = $attributeByValue[$vid] ?? null;
                if ($attrId === null) {
                    continue;
                }
                if (isset($byAttribute[$attrId])) {
                    $v->errors()->add(
                        "variations.$i.attribute_value_ids",
                        'A variation can only have one value per attribute.'
                    );

                    continue 2;
                }
                $byAttribute[$attrId] = $vid;
            }

            // duplicate combination check
            ksort($byAttribute);
            $key = implode('|', $byAttribute);
            if (isset($seen[$key])) {
                $v->errors()->add(
                    "variations.$i.attribute_value_ids",
                    'This combination is already used by another variation.'
                );
            }
            $seen[$key] = true;
        }
    }

    public function messages(): array
    {
        return [
            // Product-level
            'name.required' => 'Please provide a product name.',
            'name.string' => 'The product name must be text.',
            'name.max' => 'The product name is too long — keep it under 255 characters.',

            'sku.required' => 'Please provide a product SKU.',
            'sku.string' => 'The product SKU must be text.',
            'sku.max' => 'The product SKU is too long — keep it under 100 characters.',
            'sku.unique' => 'This product SKU is already in use — choose a different one.',

            'category_id.required' => 'Please pick a category for this product.',
            'category_id.uuid' => 'The selected category is not valid.',
            'category_id.exists' => 'The category you selected no longer exists.',

            'brand_id.uuid' => 'The selected brand is not valid.',
            'brand_id.exists' => 'The brand you selected no longer exists.',

            'short_description.max' => 'The short description is too long — keep it under 500 characters.',
            'seo_title.max' => 'The SEO title is too long — keep it under 255 characters.',
            'seo_description.max' => 'The SEO description is too long — keep it under 500 characters.',

            'is_active.boolean' => 'The status must be either active or inactive.',
            'is_featured.boolean' => 'The featured flag must be true or false.',

            // Images
            'images.array' => 'Images must be submitted as a list.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.max' => 'Each image is too large — please keep files under 4 MB.',

            // Variations
            'variations.required' => 'Please add at least one variation. Every product must have stock options.',
            'variations.array' => 'Variations must be a list.',
            'variations.min' => 'Please add at least one variation. Every product must have stock options.',

            'variations.*.sku.required' => 'Every variation needs a SKU.',
            'variations.*.sku.string' => 'Variation SKUs must be text.',
            'variations.*.sku.max' => 'Variation SKUs are too long — keep them under 100 characters.',
            'variations.*.sku.distinct' => 'Two variations have the same SKU — they must be unique.',
            'variations.*.sku.unique' => 'This variation SKU is already used by another product.',

            'variations.*.price.price' => 'Please enter variant price.',
            'variations.*.price.numeric' => 'Variation prices must be numbers.',
            'variations.*.price.min' => 'Variation prices cannot be negative.',

            'variations.*.sale_price.numeric' => 'Variation sale prices must be numbers.',
            'variations.*.sale_price.min' => 'Variation sale prices cannot be negative.',

            'variations.*.stock_quantity.required' => 'Every variation needs a stock quantity.',
            'variations.*.stock_quantity.integer' => 'Stock must be a whole number.',
            'variations.*.stock_quantity.min' => 'Stock cannot be negative.',

            'variations.*.low_stock_threshold.integer' => 'The low stock threshold must be a whole number.',
            'variations.*.low_stock_threshold.min' => 'The low stock threshold cannot be negative.',

            'variations.*.attribute_value_ids.required' => 'Pick at least one attribute value for this variation.',
            'variations.*.attribute_value_ids.array' => 'Attribute values must be a list.',
            'variations.*.attribute_value_ids.min' => 'Pick at least one attribute value for this variation.',
            'variations.*.attribute_value_ids.*.uuid' => 'One of the selected attribute values is not valid.',
            'variations.*.attribute_value_ids.*.exists' => 'One of the selected attribute values no longer exists.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'product name',
            'sku' => 'product SKU',
            'category_id' => 'category',
            'brand_id' => 'brand',
            'short_description' => 'short description',
            'description' => 'description',
            'seo_title' => 'SEO title',
            'seo_description' => 'SEO description',
            'images' => 'images',
            'variations' => 'variations',
        ];
    }
}
