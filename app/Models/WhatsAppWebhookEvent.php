<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppWebhookEvent extends Model
{
    protected $fillable = [
        'event_key',
        'wamid',
        'event_status',
        'payload',
        'processing_status',
        'processed_at',
        'processing_error',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }
}
