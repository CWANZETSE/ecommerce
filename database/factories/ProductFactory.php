<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name) . '-' . Str::random(4),
            'sku' => strtoupper(Str::random(8)),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraphs(2, true),
            'price' => fake()->numberBetween(500, 50000),
            'is_active' => true,
            'is_featured' => false,
            'show_on_homepage' => false,
        ];
    }

    public function withVariation(int $stock = 10): static
    {
        return $this->afterCreating(function (Product $product) use ($stock) {
            ProductVariation::factory()->create([
                'product_id' => $product->id,
                'stock_quantity' => $stock,
            ]);
        });
    }

    public function outOfStock(): static
    {
        return $this->withVariation(0);
    }
}