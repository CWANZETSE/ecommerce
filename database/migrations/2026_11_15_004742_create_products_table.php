<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('category_id')->constrained('categories')->restrictOnDelete();
            $table->foreignUuid('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('show_on_homepage')->default(false);
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'is_active']);
            $table->index(['brand_id', 'is_active']);
            $table->index(['is_active', 'is_featured']);
            $table->index('price');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};