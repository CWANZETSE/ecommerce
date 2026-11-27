<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SmsMessageLog extends Model
{
    protected $table = 'sms_messages';

    protected $fillable = [
        'notifiable_type',
        'notifiable_id',
        'notification_id',
        'notification_type',
        'recipient',
        'message_id',
        'status',
        'status_code',
        'cost',
        'network_code',
        'failure_reason',
        'provider_response',
        'sent_at',
        'delivered_at',
        'failed_at',
    ];

    protected function casts(): array
    {
        return [
            'provider_response' => 'array',
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo(
            __FUNCTION__,
            'notifiable_type',
            'notifiable_id'
        );
    }
}