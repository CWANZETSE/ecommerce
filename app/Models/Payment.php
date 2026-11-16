<?php

namespace App\Models;

use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasUuids;

    protected $fillable = [
        'ulid', 'order_id', 'provider', 'amount', 'currency', 'status',
        'checkout_request_id', 'merchant_request_id', 'provider_reference',
        'idempotency_key', 'raw_request', 'raw_response',
        'failure_reason', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'provider' => PaymentProvider::class,
            'status' => PaymentStatus::class,
            'amount' => 'decimal:2',
            'raw_request' => 'array',
            'raw_response' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(PaymentAttempt::class);
    }

    public function isPending(): bool
    {
        return $this->status === PaymentStatus::Pending;
    }

    public function markSucceeded(string $receipt): void
    {
        $this->update([
            'status' => PaymentStatus::Succeeded,
            'provider_reference' => $receipt,
            'paid_at' => now(),
        ]);
    }

    public function markFailed(string $reason): void
    {
        $this->update([
            'status' => PaymentStatus::Failed,
            'failure_reason' => $reason,
        ]);
    }
}