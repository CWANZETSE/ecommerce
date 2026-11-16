<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->unique();
            $table->enum('type', ['percent', 'fixed']);
            $table->decimal('value', 12, 2);
            $table->decimal('min_order_amount', 12, 2)->nullable();
            $table->decimal('max_discount', 12, 2)->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('per_customer_limit')->default(1);
            $table->boolean('first_order_only')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'expires_at']);
        });

        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('coupon_id')->constrained('coupons')->cascadeOnDelete();
            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_email');
            $table->decimal('discount_amount', 12, 2);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['coupon_id', 'user_id']);
            $table->index(['coupon_id', 'customer_email']);
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupons');
    }
};