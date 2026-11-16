<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignUuid('variation_id')->nullable()->constrained('product_variations')->cascadeOnDelete();
            $table->enum('type', [
                'initial',
                'receive',
                'adjust',
                'damage',
                'sale',
                'return',
                'refund_return',
            ]);
            $table->integer('quantity_change'); // signed: + receive, - sale
            $table->integer('previous_quantity');
            $table->integer('new_quantity');
            $table->string('reason')->nullable();
            $table->string('reference_type')->nullable();
            $table->uuid('reference_id')->nullable();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['variation_id', 'created_at']);
            $table->index(['product_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};