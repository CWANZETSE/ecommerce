<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReview extends Model
{
    use HasUuids;

    protected $fillable = [
        'product_id', 'user_id', 'order_item_id', 'rating',
        'title', 'body', 'is_verified_purchase', 'status', 'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_verified_purchase' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }
}