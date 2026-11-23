
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_webhook_events', function (Blueprint $table) {
            $table->id();

            // Prevent the same webhook event being stored twice.
            $table->string('event_key', 64)->unique();

            // Meta message identification.
            $table->string('wamid', 255)->nullable();
            $table->string('event_status', 20)->nullable();

            // Original webhook event data.
            $table->json('payload');

            // Processing lifecycle.
            $table->string('processing_status', 20)->default('pending');
            $table->timestamp('processed_at')->nullable();
            $table->text('processing_error')->nullable();

            $table->timestamps();

            $table->index('wamid');
            $table->index('processing_status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_webhook_events');
    }
};
