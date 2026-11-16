<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AddressController extends Controller
{
    public function index(): Response
    {
        $addresses = auth()->user()->addresses()->orderByDesc('is_default')->orderBy('label')->get();

        return Inertia::render('Customer/Addresses/Index', [
            'addresses' => $addresses,
            'counties' => \App\Models\ShippingZone::where('is_active', true)->orderBy('name')->get(['name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['user_id'] = auth()->id();
        $data['is_default'] = $data['is_default'] ?? false;

        DB::transaction(function () use ($data) {
            // If this is the first address, force default
            $count = auth()->user()->addresses()->count();
            if ($count === 0) {
                $data['is_default'] = true;
            }

            // If setting as default, unset the current default
            if ($data['is_default']) {
                auth()->user()->addresses()->update(['is_default' => false]);
            }

            CustomerAddress::create($data);
        });

        return back()->with('success', 'Address added.');
    }

    public function update(Request $request, CustomerAddress $address): RedirectResponse
    {
        abort_unless($address->user_id === auth()->id(), 403);

        $data = $this->validated($request);

        DB::transaction(function () use ($address, $data) {
            if (! empty($data['is_default']) && ! $address->is_default) {
                auth()->user()->addresses()
                    ->where('id', '!=', $address->id)
                    ->update(['is_default' => false]);
            }

            $address->update($data);
        });

        return back()->with('success', 'Address updated.');
    }

    public function destroy(CustomerAddress $address): RedirectResponse
    {
        abort_unless($address->user_id === auth()->id(), 403);

        $wasDefault = $address->is_default;
        $address->delete();

        // If we just deleted the default, promote the next address
        if ($wasDefault) {
            $next = auth()->user()->addresses()->orderBy('created_at')->first();
            $next?->update(['is_default' => true]);
        }

        return back()->with('success', 'Address removed.');
    }

    public function setDefault(CustomerAddress $address): RedirectResponse
    {
        abort_unless($address->user_id === auth()->id(), 403);

        DB::transaction(function () use ($address) {
            auth()->user()->addresses()->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });

        return back()->with('success', 'Default address updated.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'label' => ['nullable', 'string', 'max:50'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'county' => ['required', 'string', 'max:100'],
            'town' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'instructions' => ['nullable', 'string', 'max:500'],
            'is_default' => ['boolean'],
        ], [
            'recipient_name.required' => 'Please enter the recipient name.',
            'phone.required' => 'Please enter a phone number for delivery.',
            'county.required' => 'Please select a county.',
            'town.required' => 'Please enter the town.',
            'address.required' => 'Please enter the delivery address.',
        ]);
    }
}