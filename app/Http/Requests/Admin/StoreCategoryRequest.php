<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => [
                'nullable',
                'uuid',
                Rule::exists('categories', 'id'),
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $parent = Category::find($value);
                        if ($parent?->parent_id !== null) {
                            $fail('Categories can only nest two levels deep — pick a top-level category.');
                        }
                    }
                },
            ],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please provide a category name.',
            'name.string' => 'The category name must be text.',
            'name.max' => 'The category name is too long — keep it under 255 characters.',

            'parent_id.uuid' => 'The selected parent category is not valid.',
            'parent_id.exists' => 'The parent category you selected no longer exists.',

            'image.image' => 'The category image must be an image file.',
            'image.max' => 'The category image is too large — please keep it under 2 MB.',

            'sort_order.integer' => 'The sort order must be a whole number.',
            'sort_order.min' => 'The sort order cannot be negative.',

            'seo_title.max' => 'The SEO title is too long — keep it under 255 characters.',
            'seo_description.max' => 'The SEO description is too long — keep it under 500 characters.',

            'is_active.boolean' => 'The status must be either active or inactive.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'category name',
            'parent_id' => 'parent category',
            'image' => 'category image',
            'is_active' => 'status',
            'sort_order' => 'sort order',
            'seo_title' => 'SEO title',
            'seo_description' => 'SEO description',
        ];
    }
}