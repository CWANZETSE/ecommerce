<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsDeliveryReport extends Model
{
    protected $fillable = [
        'event_key',
        'message_id',
        'status',
        'phone_number',
        'network_code',
        'failure_reason',
        'retry_count',
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