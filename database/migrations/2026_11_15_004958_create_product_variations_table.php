<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('sku')->unique();
            $table->decimal('price', 12, 2)->nullable();
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->boolean('is_default')->default(false);
            $table->integer('stock_quantity')->default(0);
            $table->unsignedInteger('low_stock_threshold')->nullable();
            $table->timestamps();

            $table->index('product_id');
        });

        Schema::create('product_variation_attribute_values', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('variation_id')->constrained('product_variations')->cascadeOnDelete();
            $table->foreignUuid('attribute_id')->constrained('product_attributes')->cascadeOnDelete();
            $table->foreignUuid('attribute_value_id')->constrained('product_attribute_values')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['variation_id', 'attribute_id'], 'var_attr_unique');
            $table->index(['attribute_value_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variation_attribute_values');
        Schema::dropIfExists('product_variations');
    }
};