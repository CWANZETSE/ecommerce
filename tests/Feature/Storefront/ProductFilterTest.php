<?php

namespace Tests\Feature\Storefront;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_price_range_filters_products(): void
    {
        Product::factory()->withVariation()->create(['price' => 500, 'is_active' => true]);
        Product::factory()->withVariation()->create(['price' => 5000, 'is_active' => true]);
        Product::factory()->withVariation()->create(['price' => 50000, 'is_active' => true]);

        $this->get('/customer/products?min_price=1000&max_price=10000')
            ->assertInertia(fn ($p) => $p->has('products.data', 1));
    }

    public function test_only_active_products_are_listed(): void
    {
        Product::factory()->withVariation()->create(['is_active' => true]);
        Product::factory()->withVariation()->create(['is_active' => false]);

        $this->get('/customer/products')
            ->assertInertia(fn ($p) => $p->has('products.data', 1));
    }

    public function test_brand_filter(): void
    {
        $brand = Brand::factory()->create(['slug' => 'samsung']);
        Product::factory()->withVariation()->create(['brand_id' => $brand->id, 'is_active' => true]);
        Product::factory()->withVariation()->create(['is_active' => true]);

        $this->get('/customer/products?brand=samsung')
            ->assertInertia(fn ($p) => $p->has('products.data', 1));
    }
}