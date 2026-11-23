
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();

            // The model that received the notification (User, etc.).
            // String columns support UUID and other key types.
            $table->string('notifiable_type');
            $table->string('notifiable_id', 64);

            // Laravel notification identification.
            $table->string('notification_id', 64)->nullable();
            $table->string('notification_type')->nullable();

            // WhatsApp recipient and Meta message identification.
            $table->string('recipient', 32);
            $table->string('wamid', 255)->nullable()->unique();

            // Message information.
            $table->string('message_type', 20);
            $table->string('template_name')->nullable();

            // Delivery tracking.
            $table->string('status', 20)->default('pending');

            // Provider error information.
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();

            // Optional response returned by Meta.
            $table->json('provider_response')->nullable();

            // Delivery timestamps.
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            $table->timestamps();

            // Query indexes.
            $table->index(
                ['notifiable_type', 'notifiable_id'],
                'whatsapp_messages_notifiable_index'
            );

            $table->index('notification_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
