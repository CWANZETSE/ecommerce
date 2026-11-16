<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ShippingZone extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'fee',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'fee' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}