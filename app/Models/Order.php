<?php

namespace App\Models;

use App\Enums\OrderFulfilmentStatus;
use App\Enums\OrderPaymentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasUuids,HasFactory;

    protected $fillable = [
        'ulid', 'order_number', 'user_id',
        'customer_name', 'customer_email', 'customer_phone', 'shipping_address',
        'subtotal', 'shipping_fee', 'grand_total',
        'payment_status', 'fulfilment_status',
        'idempotency_key', 'paid_at', 'cancelled_at','coupon_code','discount_total'
    ];

    protected function casts(): array
    {
        return [
            'shipping_address' => 'array',
            'subtotal' => 'decimal:2',
            'shipping_fee' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'payment_status' => OrderPaymentStatus::class,
            'fulfilment_status' => OrderFulfilmentStatus::class,
            'paid_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'discount_total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at');
    }

    public function markPaid(string $receipt): void
    {
        $previous = $this->payment_status;
        $this->update([
            'payment_status' => OrderPaymentStatus::Paid,
            'fulfilment_status' => OrderFulfilmentStatus::Processing,
            'paid_at' => now(),
        ]);
        $this->recordStatus($previous?->value, 'paid', 'payment');
        $this->recordStatus('pending', 'processing', 'fulfilment');
    }
    public function couponUsages(): HasMany
{
    return $this->hasMany(CouponUsage::class);
}

    public function recordStatus(?string $from, string $to, string $type, ?string $note = null): void
    {
        OrderStatusHistory::create([
            'order_id' => $this->id,
            'from_status' => $from,
            'to_status' => $to,
            'type' => $type,
            'note' => $note,
            'user_id' => auth()->id(),
        ]);
    }
}