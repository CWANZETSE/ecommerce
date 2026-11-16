<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('token', 64)->nullable()->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cart_id')->constrained('carts')->cascadeOnDelete();
            $table->foreignUuid('variation_id')->constrained('product_variations')->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamps();

            $table->unique(['cart_id', 'variation_id']);
            $table->index('variation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};