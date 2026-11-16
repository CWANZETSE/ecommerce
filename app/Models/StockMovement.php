<?php

namespace App\Models;

use App\Enums\StockMovementType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'variation_id',
        'type',
        'quantity_change',
        'previous_quantity',
        'new_quantity',
        'reason',
        'reference_type',
        'reference_id',
        'user_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => StockMovementType::class,
            'quantity_change' => 'integer',
            'previous_quantity' => 'integer',
            'new_quantity' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class, 'variation_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}