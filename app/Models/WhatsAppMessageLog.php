<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WhatsAppMessageLog extends Model
{
    protected $table = 'whatsapp_messages';

    protected $fillable = [
        'notifiable_type',
        'notifiable_id',
        'notification_id',
        'notification_type',
        'recipient',
        'wamid',
        'message_type',
        'template_name',
        'status',
        'error_code',
        'error_message',
        'provider_response',
        'sent_at',
        'delivered_at',
        'read_at',
        'failed_at',
    ];

    protected function casts(): array
    {
        return [
            'provider_response' => 'array',
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
            'read_at' => 'datetime',
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
