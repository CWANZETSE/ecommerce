<?php

namespace Tests\Feature\Storefront;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_filters_by_name_and_sku(): void
    {
        Product::factory()->withVariation()->create(['name' => 'iPhone 15', 'sku' => 'PHN-002', 'is_active' => true]);
        Product::factory()->withVariation()->create(['name' => 'Samsung', 'sku' => 'PHN-001', 'is_active' => true]);

        $this->get('/customer/products?search=iPhone')
            ->assertInertia(fn ($p) => $p->has('products.data', 1));

        $this->get('/customer/products?search=PHN-001')
            ->assertInertia(fn ($p) => $p->has('products.data', 1));
    }

    public function test_pagination_limits_results(): void
    {
        Product::factory()->count(30)->withVariation()->create(['is_active' => true]);

        $this->get('/customer/products')
            ->assertInertia(fn ($p) => $p->has('products.data', 24));
    }
}