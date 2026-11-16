<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasUuids;

    protected $fillable = [
        'code', 'type', 'value', 'min_order_amount', 'max_discount',
        'starts_at', 'expires_at', 'usage_limit', 'per_customer_limit',
        'first_order_only', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'usage_limit' => 'integer',
            'per_customer_limit' => 'integer',
            'first_order_only' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function hasStarted(): bool
    {
        return $this->starts_at === null || $this->starts_at->isPast();
    }

    public function hasUsages(): bool
{
    return $this->usages()->exists();
}

    public function isExhausted(): bool
    {
        return $this->usage_limit !== null
            && $this->usages()->count() >= $this->usage_limit;
    }
}