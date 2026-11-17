<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class StoreSettingController extends Controller
{
    /**
     * Keys that are stored as 'true'/'false'.
     */
    private const BOOLEAN_KEYS = [
        'tax_enabled',
        'maintenance_mode',
        'free_shipping_enabled',
    ];

    public function index(): Response
    {
        $groups = StoreSetting::where('is_secret', false)
            ->orderBy('group')
            ->orderBy('key')
            ->get()
            ->groupBy('group')
            ->map(fn ($items) => $items->map(fn ($s) => [
                'id' => $s->id,
                'key' => $s->key,
                'value' => $s->value,
                'type' => $this->typeFor($s->key),
                'group' => $s->group,
                'image_url' => $s->key === 'store_logo' && $s->value
                    ? asset('storage/'.$s->value)
                    : null,
            ])->values());

        return Inertia::render('Admin/Settings/Index', [
            'groups' => $groups,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*.key' => ['required', 'string', 'max:100'],
            'settings.*.value' => ['nullable'],
            'logo' => [
                'nullable',
                'file',
                'max:2048',
                'mimes:jpg,jpeg,png,gif,webp,svg',
                'mimetypes:image/jpeg,image/png,image/gif,image/webp,image/svg+xml,text/xml,application/xml',
            ],
            'remove_logo' => ['boolean'],
        ], [
            'logo.mimes' => 'The logo must be a JPG, PNG, GIF, WebP, or SVG file.',
            'logo.mimetypes' => 'The logo must be a valid image file (JPG, PNG, GIF, WebP, or SVG).',
            'logo.max' => 'The logo must not exceed 2 MB.',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $existing = StoreSetting::where('key', 'store_logo')->first();

            if ($existing?->value) {
                Storage::disk('public')->delete($existing->value);
            }

            $path = $request->file('logo')->store('branding', 'public');
            StoreSetting::set('store_logo', $path, 'general');
        }

        // Handle explicit logo removal
        if ($request->boolean('remove_logo')) {
            $existing = StoreSetting::where('key', 'store_logo')->first();
            if ($existing?->value) {
                Storage::disk('public')->delete($existing->value);
            }
            StoreSetting::where('key', 'store_logo')->update(['value' => null]);
        }

        // Skip 'store_logo' from the generic settings loop — handled above
        foreach ($data['settings'] as $row) {
            if ($row['key'] === 'store_logo') {
                continue;
            }

            $value = $row['value'];

            if (in_array($row['key'], self::BOOLEAN_KEYS, true)) {
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
            } elseif (is_bool($value)) {
                $value = $value ? '1' : '0';
            } elseif ($value !== null) {
                $value = (string) $value;
            }

            StoreSetting::where('key', $row['key'])->update(['value' => $value]);
        }

        return back()->with('success', 'Settings saved.');
    }

    private function typeFor(string $key): string
    {
        return match ($key) {
            'store_logo' => 'image',
            'tax_enabled', 'maintenance_mode', 'free_shipping_enabled' => 'boolean',
            'featured_products_limit', 'new_arrivals_limit', 'low_stock_threshold' => 'number',
            default => 'text',
        };
    }
}
