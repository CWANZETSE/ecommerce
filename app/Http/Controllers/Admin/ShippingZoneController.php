<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShippingZoneController extends Controller
{
    public function index(Request $request): Response
    {
        $zones = ShippingZone::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('name')
            ->get()
            ->map(fn (ShippingZone $z) => [
                'id' => $z->id,
                'name' => $z->name,
                'fee' => $z->fee,
                'is_active' => $z->is_active,
            ]);

        return Inertia::render('Admin/Shipping/Index', [
            'zones' => $zones,
            'filters' => ['search' => $request->get('search')],
            'stats' => [
                'total' => ShippingZone::count(),
                'active' => ShippingZone::where('is_active', true)->count(),
                'free' => ShippingZone::where('fee', 0)->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:shipping_zones,name'],
            'fee' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ], [
            'name.required' => 'Please provide a zone or county name.',
            'name.unique' => 'A shipping zone with this name already exists.',
            'fee.required' => 'Please enter a shipping fee.',
            'fee.min' => 'The shipping fee cannot be negative.',
        ]);

        ShippingZone::create([
            'name' => $data['name'],
            'fee' => number_format((float) $data['fee'], 2, '.', ''),
            'is_active' => $data['is_active'] ?? true,
        ]);

        return back()->with('success', 'Shipping zone created.');
    }

    public function update(Request $request, ShippingZone $zone): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:shipping_zones,name,'.$zone->id],
            'fee' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ], [
            'name.required' => 'Please provide a zone or county name.',
            'name.unique' => 'A shipping zone with this name already exists.',
            'fee.required' => 'Please enter a shipping fee.',
            'fee.min' => 'The shipping fee cannot be negative.',
        ]);

        $zone->update([
            'name' => $data['name'],
            'fee' => number_format((float) $data['fee'], 2, '.', ''),
            'is_active' => $data['is_active'] ?? $zone->is_active,
        ]);

        return back()->with('success', 'Shipping zone updated.');
    }

    public function toggle(ShippingZone $zone): RedirectResponse
    {
        $zone->update(['is_active' => ! $zone->is_active]);

        return back()->with('success', $zone->is_active ? 'Zone enabled.' : 'Zone disabled.');
    }

    public function destroy(ShippingZone $zone): RedirectResponse
    {
        $zone->delete();

        return back()->with('success', 'Shipping zone deleted.');
    }

    public function bulkUpdate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'zones' => ['required', 'array'],
            'zones.*.id' => ['required', 'uuid', 'exists:shipping_zones,id'],
            'zones.*.fee' => ['required', 'numeric', 'min:0'],
            'zones.*.is_active' => ['boolean'],
        ]);

        foreach ($data['zones'] as $row) {
            ShippingZone::whereKey($row['id'])->update([
                'fee' => number_format((float) $row['fee'], 2, '.', ''),
                'is_active' => $row['is_active'] ?? true,
            ]);
        }

        return back()->with('success', 'Shipping fees updated.');
    }
}
