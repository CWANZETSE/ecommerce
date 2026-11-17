<?php

namespace App\Http\Middleware;

use App\Models\StoreSetting;
use App\Services\CartService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => fn () => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'email_verified_at' => $request->user()->email_verified_at,
                    'role' => $request->user()->role?->value,
                    'created_at' => $request->user()->created_at,
                    'updated_at' => $request->user()->updated_at,
                ] : null,
            ],

            // Store-wide settings — available to guests AND logged-in users
            'store_name' => fn () => StoreSetting::get('store_name', 'My Store'),
            'store_tagline' => fn () => StoreSetting::get('store_tagline', ''),
            'store_logo_url' => function () {
                $logo = StoreSetting::get('store_logo');
                return $logo ? asset('storage/' . $logo) : null;
            },
            'currency' => fn () => StoreSetting::get('currency', 'KES'),
            'free_shipping_enabled' => fn () => StoreSetting::get('free_shipping_enabled', 'true') === 'true',
            'free_shipping_threshold' => fn () => (int) StoreSetting::get('free_shipping_threshold', 5000),
            'featured_products_limit' => fn () => (int) StoreSetting::get('featured_products_limit', 8),
            'new_arrivals_limit' => fn () => (int) StoreSetting::get('new_arrivals_limit', 8),

            'cart_count' => function () {
                $cart = app(CartService::class)->current();
                return $cart ? (int) $cart->items()->sum('quantity') : 0;
            },
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
            ],
        ]);
    }
}