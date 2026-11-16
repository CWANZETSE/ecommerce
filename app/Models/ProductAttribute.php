<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductAttribute extends Model
{
    use HasUuids,HasFactory;

    protected $fillable = ['name', 'slug'];

    public function values(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class, 'attribute_id')->orderBy('sort_order');
    }
}