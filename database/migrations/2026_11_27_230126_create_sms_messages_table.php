<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_messages', function (Blueprint $table) {
            $table->id();

            // The model that received the notification.
            $table->string('notifiable_type');
            $table->string('notifiable_id', 64);

            // Laravel notification identification.
            $table->string('notification_id', 64)->nullable();
            $table->string('notification_type')->nullable();

            // Africa's Talking message identification.
            $table->string('recipient', 32);
            $table->string('message_id', 255)->nullable()->unique();

            // Provider response and delivery state.
            $table->string('status', 30)->default('pending');
            $table->unsignedInteger('status_code')->nullable();
            $table->string('cost', 32)->nullable();
            $table->string('network_code', 32)->nullable();

            // Failure information.
            $table->text('failure_reason')->nullable();

            // Provider response only; never store the SMS message body.
            $table->json('provider_response')->nullable();

            // Delivery timestamps.
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            $table->timestamps();

            $table->index(
                ['notifiable_type', 'notifiable_id'],
                'sms_messages_notifiable_index'
            );

            $table->index('notification_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_messages');
    }
};