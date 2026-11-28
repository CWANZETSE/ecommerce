<?php

namespace App\Http\Requests\Admin;

use App\Models\ProductAttributeValue;
use App\Models\ProductVariation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
{
    $productId = $this->route('product')?->id;

    return [
        'name' => ['required', 'string', 'max:255'],
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
        'variations.*.id' => ['nullable', 'uuid'],
        'variations.*.price' => ['required', 'numeric', 'min:0'],
        'variations.*.sale_price' => ['nullable', 'numeric', 'min:0'],
        'variations.*.stock_quantity' => ['required', 'integer', 'min:0'],
        'variations.*.low_stock_threshold' => ['nullable', 'integer', 'min:0'],
        'variations.*.attribute_value_ids' => ['required', 'array', 'min:1'],
        'variations.*.attribute_value_ids.*' => ['uuid', Rule::exists('product_attribute_values', 'id')],
        'variations.*.is_default' => ['required', 'boolean'],
    ];
}

public function withValidator(Validator $validator): void
{
    $validator->after(function (Validator $v) {
        $rows = (array) $this->input('variations', []);
        $this->validateAttributeCombinations($v, $rows);
    });
}



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
            'name.required' => 'Please provide a product name.',
            'name.max' => 'The product name is too long — keep it under 255 characters.',

            'sku.required' => 'Please provide a product SKU.',
            'sku.max' => 'The product SKU is too long — keep it under 100 characters.',
            'sku.unique' => 'This product SKU is already in use — choose a different one.',

            'category_id.required' => 'Please pick a category for this product.',
            'category_id.exists' => 'The category you selected no longer exists.',

            'brand_id.exists' => 'The brand you selected no longer exists.',

            'seo_title.max' => 'The SEO title is too long — keep it under 255 characters.',
            'seo_description.max' => 'The SEO description is too long — keep it under 500 characters.',

            'is_active.boolean' => 'The status must be either active or inactive.',
            'is_featured.boolean' => 'The featured flag must be true or false.',

            'images.*.image' => 'Each file must be an image.',
            'images.*.max' => 'Each image is too large — please keep files under 4 MB.',

            'variations.required' => 'Please add at least one variation. Every product must have stock options.',
            'variations.min' => 'Please add at least one variation. Every product must have stock options.',

            'variations.*.sku.required' => 'Every variation needs a SKU.',
            'variations.*.sku.distinct' => 'Two variations have the same SKU — they must be unique.',
            
            'variations.*.price.required' => 'Please enter variant price.',
            'variations.*.price.numeric' => 'Variation prices must be numbers.',
            'variations.*.price.min' => 'Variation prices cannot be negative.',

            'variations.*.sale_price.numeric' => 'Variation sale prices must be numbers.',

            'variations.*.stock_quantity.required' => 'Every variation needs a stock quantity.',
            'variations.*.stock_quantity.integer' => 'Stock must be a whole number.',
            'variations.*.stock_quantity.min' => 'Stock cannot be negative.',

            'variations.*.low_stock_threshold.integer' => 'The low stock threshold must be a whole number.',
            'variations.*.low_stock_threshold.min' => 'The low stock threshold cannot be negative.',

            'variations.*.attribute_value_ids.required' => 'Pick at least one attribute value for this variation.',
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
            'seo_title' => 'SEO title',
            'seo_description' => 'SEO description',
            'variations' => 'variations',
        ];
    }
}
