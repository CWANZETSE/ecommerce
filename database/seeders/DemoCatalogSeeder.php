<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductVariation;
use App\Models\ProductVariationAttributeValue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $electronics = Category::firstOrCreate(['name' => 'Electronics'], ['slug' => 'electronics', 'is_active' => true]);
        $fashion = Category::firstOrCreate(['name' => 'Fashion'], ['slug' => 'fashion', 'is_active' => true]);
        $home = Category::firstOrCreate(['name' => 'Home'], ['slug' => 'home', 'is_active' => true]);

        $phones = Category::firstOrCreate(['name' => 'Phones', 'parent_id' => $electronics->id], ['slug' => 'phones', 'is_active' => true]);
        Category::firstOrCreate(['name' => 'Laptops', 'parent_id' => $electronics->id], ['slug' => 'laptops', 'is_active' => true]);
        Category::firstOrCreate(['name' => 'Men', 'parent_id' => $fashion->id], ['slug' => 'men', 'is_active' => true]);
        Category::firstOrCreate(['name' => 'Women', 'parent_id' => $fashion->id], ['slug' => 'women', 'is_active' => true]);

        $samsung = Brand::firstOrCreate(['name' => 'Samsung'], ['slug' => 'samsung', 'is_active' => true]);
        $apple = Brand::firstOrCreate(['name' => 'Apple'], ['slug' => 'apple', 'is_active' => true]);
        $generic = Brand::firstOrCreate(['name' => 'Generic'], ['slug' => 'generic', 'is_active' => true]);

        $storage = ProductAttribute::firstOrCreate(['name' => 'Storage'], ['slug' => 'storage']);
        $v128 = ProductAttributeValue::firstOrCreate(['attribute_id' => $storage->id, 'value' => '128 GB']);
        $v256 = ProductAttributeValue::firstOrCreate(['attribute_id' => $storage->id, 'value' => '256 GB']);
        $v512 = ProductAttributeValue::firstOrCreate(['attribute_id' => $storage->id, 'value' => '512 GB']);

        $size = ProductAttribute::firstOrCreate(['name' => 'Size'], ['slug' => 'size']);
        $sSmall = ProductAttributeValue::firstOrCreate(['attribute_id' => $size->id, 'value' => 'S']);
        $sMedium = ProductAttributeValue::firstOrCreate(['attribute_id' => $size->id, 'value' => 'M']);
        $sLarge = ProductAttributeValue::firstOrCreate(['attribute_id' => $size->id, 'value' => 'L']);

        $capacity = ProductAttribute::firstOrCreate(['name' => 'Capacity'], ['slug' => 'capacity']);
        $c2L = ProductAttributeValue::firstOrCreate(['attribute_id' => $capacity->id, 'value' => '2 L']);
        $c5L = ProductAttributeValue::firstOrCreate(['attribute_id' => $capacity->id, 'value' => '5 L']);

        $galaxy = Product::firstOrCreate(['sku' => 'PHN-001'], [
            'category_id' => $phones->id,
            'brand_id' => $samsung->id,
            'name' => 'Samsung Galaxy A15',
            'slug' => 'samsung-galaxy-a15',
            'price' => 24999,
            'is_active' => true,
            'is_featured' => true,
            'short_description' => 'Affordable 5G smartphone with 6.5" display',
        ]);
        $this->variation($galaxy, 'PHN-001-128', 24999, 15, [$v128]);
        $this->variation($galaxy, 'PHN-001-256', 29999, 8, [$v256]);

        $iphone = Product::firstOrCreate(['sku' => 'PHN-002'], [
            'category_id' => $phones->id,
            'brand_id' => $apple->id,
            'name' => 'iPhone 15',
            'slug' => 'iphone-15',
            'price' => 129999,
            'sale_price' => 119999,
            'is_active' => true,
            'is_featured' => true,
            'short_description' => 'A16 Bionic chip, 48MP camera',
        ]);
        $this->variation($iphone, 'PHN-002-128', 119999, 5, [$v128]);
        $this->variation($iphone, 'PHN-002-256', 134999, 3, [$v256]);
        $this->variation($iphone, 'PHN-002-512', 159999, 0, [$v512]);

        $pot = Product::firstOrCreate(['sku' => 'HOM-001'], [
            'category_id' => $home->id,
            'brand_id' => $generic->id,
            'name' => 'Stainless Steel Cooking Pot',
            'slug' => 'stainless-steel-cooking-pot',
            'price' => 3500,
            'is_active' => true,
            'short_description' => 'Durable stainless steel pot',
        ]);
        $this->variation($pot, 'HOM-001-2L', 3500, 0, [$c2L]);
        $this->variation($pot, 'HOM-001-5L', 5200, 4, [$c5L]);

        $tshirt = Product::firstOrCreate(['sku' => 'FSH-001'], [
            'category_id' => $fashion->id,
            'brand_id' => $generic->id,
            'name' => 'Cotton T-Shirt',
            'slug' => 'cotton-t-shirt',
            'price' => 1500,
            'is_active' => true,
            'short_description' => '100% cotton, comfortable fit',
        ]);
        $this->variation($tshirt, 'FSH-001-S', 1500, 10, [$sSmall]);
        $this->variation($tshirt, 'FSH-001-M', 1500, 8, [$sMedium]);
        $this->variation($tshirt, 'FSH-001-L', 1500, 0, [$sLarge]);
    }



    private function variation(Product $product, string $sku, float $price, int $stock, array $values): void
{
    $variation = ProductVariation::firstOrCreate(
        ['sku' => $sku],
        [
            'product_id' => $product->id,
            'price' => $price,
            'stock_quantity' => 0, // ← create with 0, then write initial movement
        ]
    );

    foreach ($values as $value) {
        ProductVariationAttributeValue::updateOrCreate(
            ['variation_id' => $variation->id, 'attribute_id' => $value->attribute_id],
            ['id' => (string) Str::uuid(), 'attribute_value_id' => $value->id]
        );
    }

    // Record initial stock movement if none exists yet
    if ($stock > 0 && ! $variation->stockMovements()->exists()) {
        $variation->update(['stock_quantity' => $stock]);
        \App\Models\StockMovement::create([
            'product_id' => $product->id,
            'variation_id' => $variation->id,
            'type' => \App\Enums\StockMovementType::Initial,
            'quantity_change' => $stock,
            'previous_quantity' => 0,
            'new_quantity' => $stock,
            'reason' => 'Seeded initial stock',
            'user_id' => null,
        ]);
    }
}
}