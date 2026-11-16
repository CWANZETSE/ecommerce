<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
            $table->unsignedTinyInteger('rating');         // 1–5
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->boolean('is_verified_purchase')->default(false);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'product_id', 'order_item_id'], 'reviews_user_product_order_item_unique');
            $table->index(['product_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};