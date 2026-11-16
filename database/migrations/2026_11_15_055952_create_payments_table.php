<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('ulid', 26)->unique();
            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();

            $table->enum('provider', ['mpesa'])->default('mpesa');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('KES');

            $table->enum('status', [
                'pending', 'processing', 'succeeded', 'failed', 'cancelled',
            ])->default('pending');

            // M-Pesa identifiers
            $table->string('checkout_request_id')->nullable()->unique();
            $table->string('merchant_request_id')->nullable();
            $table->string('provider_reference')->nullable()->unique(); // M-Pesa receipt

            $table->string('idempotency_key', 64)->unique();

            $table->json('raw_request')->nullable();
            $table->json('raw_response')->nullable();

            $table->string('failure_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('status');
        });

        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->enum('event', ['initiate', 'callback', 'query']);
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['payment_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_attempts');
        Schema::dropIfExists('payments');
    }
};