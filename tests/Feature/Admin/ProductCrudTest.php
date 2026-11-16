<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_product(): void
{
    $admin = User::factory()->admin()->create();
    $category = \App\Models\Category::factory()->create();
    $attribute = \App\Models\ProductAttribute::factory()->create();
    $value = \App\Models\ProductAttributeValue::factory()->create(['attribute_id' => $attribute->id]);

    $response = $this->actingAs($admin)->post('/admin/products', [
        'name' => 'Test Product',
        'sku' => 'TEST-001',
        'category_id' => $category->id,
        'price' => 1000,
        'is_active' => true,
        'is_featured' => false,
        'variations' => [
            [
                'sku' => 'TEST-001-V1',
                'stock_quantity' => 10,
                'attribute_value_ids' => [$value->id],
            ],
        ],
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('products', ['sku' => 'TEST-001', 'name' => 'Test Product']);
}

public function test_slug_is_auto_generated(): void
{
    $admin = User::factory()->admin()->create();
    $category = \App\Models\Category::factory()->create();
    $attribute = \App\Models\ProductAttribute::factory()->create();
    $value = \App\Models\ProductAttributeValue::factory()->create(['attribute_id' => $attribute->id]);

    $this->actingAs($admin)->post('/admin/products', [
        'name' => 'My Cool Product',
        'sku' => 'COOL-1',
        'category_id' => $category->id,
        'price' => 500,
        'variations' => [
            [
                'sku' => 'COOL-1-V1',
                'stock_quantity' => 1,
                'attribute_value_ids' => [$value->id],
            ],
        ],
    ]);

    $this->assertDatabaseHas('products', ['slug' => 'my-cool-product']);
}

    public function test_sku_must_be_unique(): void
    {
        $admin = User::factory()->admin()->create();
        Product::factory()->create(['sku' => 'DUP-001']);
        $category = Category::factory()->create();

        $this->actingAs($admin)->post('/admin/products', [
            'name' => 'Another',
            'sku' => 'DUP-001',
            'category_id' => $category->id,
            'type' => 'simple',
            'price' => 100,
            'stock_quantity' => 1,
        ])->assertSessionHasErrors('sku');
    }

    public function test_customer_cannot_create_product(): void
    {
        $customer = User::factory()->create();
        $category = Category::factory()->create();

        $this->actingAs($customer)->post('/admin/products', [
            'name' => 'Blocked',
            'sku' => 'BLOCK-1',
            'category_id' => $category->id,
            'type' => 'simple',
            'price' => 1,
            'stock_quantity' => 1,
        ])->assertForbidden();
    }
}
