<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductVariationAttributeValue extends Pivot
{
    use HasUuids;

    protected $table = 'product_variation_attribute_values';

    public $incrementing = false;

    protected $keyType = 'string';
}