<?php

namespace Database\Factories;

use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductAttributeValueFactory extends Factory
{
    protected $model = ProductAttributeValue::class;

    public function definition(): array
    {
        return [
            'attribute_id' => ProductAttribute::factory(),
            'value' => fake()->unique()->word(),
            'sort_order' => 0,
        ];
    }
}