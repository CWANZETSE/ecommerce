<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductVariation extends Model
{
    use HasUuids,HasFactory;

    protected $fillable = [
        'product_id', 'sku', 'price', 'sale_price','is_default', 'stock_quantity', 'low_stock_threshold',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'low_stock_threshold' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductAttributeValue::class,
            'product_variation_attribute_values',
            'variation_id',
            'attribute_value_id'
        )
        ->using(ProductVariationAttributeValue::class)
        ->withTimestamps();
    }

    public function getEffectivePriceAttribute(): string
    {
        return $this->sale_price ?? $this->price ?? $this->product->price;
    }

    public function stockMovements()
{
    return $this->hasMany(\App\Models\StockMovement::class, 'variation_id');
}
}