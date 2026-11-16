<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductVariationFactory extends Factory
{
    protected $model = ProductVariation::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'sku' => strtoupper(Str::random(10)),
            'price' => null,
            'sale_price' => null,
            'stock_quantity' => 10,
            'low_stock_threshold' => 5,
        ];
    }
}