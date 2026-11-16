<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StoreSettingController extends Controller
{
    public function index(): Response
{
    $booleanKeys = ['tax_enabled', 'maintenance_mode'];

    $groups = StoreSetting::where('is_secret', false)
        ->orderBy('group')
        ->orderBy('key')
        ->get()
        ->groupBy('group')
        ->map(fn ($items) => $items->map(fn ($s) => [
            'id' => $s->id,
            'key' => $s->key,
            'value' => $s->value,
            'type' => in_array($s->key, $booleanKeys, true) ? 'boolean' : 'text',
            'group' => $s->group,
        ])->values());

    return Inertia::render('Admin/Settings/Index', [
        'groups' => $groups,
    ]);
}

    public function update(Request $request): RedirectResponse
{
    $booleanKeys = ['tax_enabled', 'maintenance_mode'];

    $data = $request->validate([
        'settings' => ['required', 'array'],
        'settings.*.key' => ['required', 'string', 'max:100'],
        'settings.*.value' => ['nullable'],
    ]);

    foreach ($data['settings'] as $row) {
        $value = $row['value'];

        if (in_array($row['key'], $booleanKeys, true)) {
            // Accept true/false, "true"/"false", 1/0
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
        } elseif (is_bool($value)) {
            // Any other field accidentally sent as boolean becomes a string
            $value = $value ? '1' : '0';
        } elseif ($value !== null) {
            $value = (string) $value;
        }

        StoreSetting::where('key', $row['key'])->update(['value' => $value]);
    }

    return back()->with('success', 'Settings saved.');
}
}