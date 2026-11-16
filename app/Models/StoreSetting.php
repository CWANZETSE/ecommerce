<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    use HasUuids;

    protected $fillable = ['key', 'value', 'group', 'is_secret'];

    protected function casts(): array
    {
        return ['is_secret' => 'boolean'];
    }

    public function scopePublic($query)
    {
        return $query->where('is_secret', false);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = self::where('key', $key)->first();
        return $setting?->value ?? $default;
    }

    public static function set(string $key, mixed $value, string $group = 'general', bool $isSecret = false): self
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group, 'is_secret' => $isSecret],
        );
    }
}