<?php

namespace Database\Factories;

use App\Models\ProductAttribute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductAttributeFactory extends Factory
{
    protected $model = ProductAttribute::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Size', 'Colour', 'Storage', 'Material', 'Capacity']);
        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::lower(Str::random(3)),
        ];
    }
}