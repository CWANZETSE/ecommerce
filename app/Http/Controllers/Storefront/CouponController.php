<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\CouponService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class CouponController extends Controller
{
    public function __construct(
        private CartService $cart,
        private CouponService $coupons,
    ) {}

    public function apply(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50'],
        ], [
            'code.required' => 'Enter a coupon code.',
        ]);

        $cart = $this->cart->current();
        if (! $cart) {
            return back()->with('error', 'Your cart is empty.');
        }

        $serialized = $this->cart->serialize($cart);

        try {
            $this->coupons->validate(
                $data['code'],
                (float) $serialized['subtotal'],
                auth()->user()?->email,
                auth()->user(),
            );
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->cart->setCoupon($data['code']);

        return back()->with('success', 'Coupon applied.');
    }

    public function remove(): RedirectResponse
    {
        $this->cart->forgetCoupon();

        return back()->with('success', 'Coupon removed.');
    }
}