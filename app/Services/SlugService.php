<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SlugService
{
    /**
     * Generate a unique slug for the given model class.
     */
    public function generate(string $name, string $modelClass, ?string $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;

        while ($this->exists($modelClass, $slug, $ignoreId)) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    private function exists(string $modelClass, string $slug, ?string $ignoreId): bool
    {
        $query = $modelClass::where('slug', $slug);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }
}