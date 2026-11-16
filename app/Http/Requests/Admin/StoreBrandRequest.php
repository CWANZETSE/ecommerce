<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please provide a brand name.',
            'name.string' => 'The brand name must be text.',
            'name.max' => 'The brand name is too long — keep it under 255 characters.',

            'logo.image' => 'The logo must be an image file (jpg, png, svg, etc.).',
            'logo.max' => 'The logo is too large — please keep it under 2 MB.',
            'logo.uploaded' => 'The logo could not be uploaded. Please try again with a smaller file.',

            'is_active.boolean' => 'The status must be either active or inactive.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'brand name',
            'logo' => 'brand logo',
            'is_active' => 'status',
        ];
    }
}