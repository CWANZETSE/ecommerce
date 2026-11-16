<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['name', 'slug', 'logo_path', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Public URL for the brand logo, or null if not set.
     * Usage in Vue: brand.logo_url
     */
    protected function logoUrl(): Attribute
    {
        return Attribute::get(
            fn () => $this->logo_path ? asset('storage/' . $this->logo_path) : null
        );
    }
}