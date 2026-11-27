<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_delivery_reports', function (Blueprint $table) {
            $table->id();

            // Idempotency key for incoming callback events.
            $table->string('event_key', 64)->unique();

            // Africa's Talking message ID.
            $table->string('message_id', 255)->index();

            // Raw provider delivery status.
            $table->string('status', 40);

            // Callback details.
            $table->string('phone_number', 32)->nullable();
            $table->string('network_code', 32)->nullable();
            $table->text('failure_reason')->nullable();
            $table->unsignedInteger('retry_count')->default(0);

            // Original callback fields, without SMS content.
            $table->json('payload');

            // Asynchronous processing state.
            $table->string('processing_status', 20)->default('pending');
            $table->timestamp('processed_at')->nullable();
            $table->text('processing_error')->nullable();

            $table->timestamps();

            $table->index('processing_status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_delivery_reports');
    }
};