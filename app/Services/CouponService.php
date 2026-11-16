<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\User;
use InvalidArgumentException;

class CouponService
{
    /**
     * Validate a coupon code and return the coupon + computed discount.
     * Throws InvalidArgumentException with a user-facing message on failure.
     *
     * @return array{coupon: Coupon, discount: float}
     */
    public function validate(
        string $code,
        float $subtotal,
        ?string $customerEmail = null,
        ?User $user = null,
    ): array {
        $code = strtoupper(trim($code));
        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon) {
            throw new InvalidArgumentException('Coupon code not found.');
        }

        if (! $coupon->is_active) {
            throw new InvalidArgumentException('This coupon is no longer active.');
        }

        if (! $coupon->hasStarted()) {
            throw new InvalidArgumentException('This coupon is not yet active.');
        }

        if ($coupon->isExpired()) {
            throw new InvalidArgumentException('This coupon has expired.');
        }

        if ($coupon->isExhausted()) {
            throw new InvalidArgumentException('This coupon has reached its usage limit.');
        }

        if ($coupon->min_order_amount !== null && $subtotal < (float) $coupon->min_order_amount) {
            throw new InvalidArgumentException(
                'This coupon requires a minimum order of KES ' . number_format((float) $coupon->min_order_amount, 2) . '.'
            );
        }

        // Per-customer limit
        if ($user) {
            $usageCount = CouponUsage::where('coupon_id', $coupon->id)
                ->where('user_id', $user->id)
                ->count();
        } elseif ($customerEmail) {
            $usageCount = CouponUsage::where('coupon_id', $coupon->id)
                ->where('customer_email', $customerEmail)
                ->count();
        } else {
            $usageCount = 0;
        }

        if ($usageCount >= $coupon->per_customer_limit) {
            throw new InvalidArgumentException('You have already used this coupon the maximum number of times.');
        }

        // First-order-only
        if ($coupon->first_order_only) {
            $hasOrders = $user
                ? Order::where('user_id', $user->id)->where('payment_status', 'paid')->exists()
                : ($customerEmail
                    ? Order::where('customer_email', $customerEmail)->where('payment_status', 'paid')->exists()
                    : false);

            if ($hasOrders) {
                throw new InvalidArgumentException('This coupon is only valid for your first order.');
            }
        }

        $discount = $this->computeDiscount($coupon, $subtotal);

        return ['coupon' => $coupon, 'discount' => $discount];
    }

    public function computeDiscount(Coupon $coupon, float $subtotal): float
    {
        $discount = match ($coupon->type) {
            'percent' => $subtotal * ((float) $coupon->value / 100),
            'fixed' => (float) $coupon->value,
        };

        if ($coupon->max_discount !== null) {
            $discount = min($discount, (float) $coupon->max_discount);
        }

        // Never discount more than the subtotal
        return round(min($discount, $subtotal), 2);
    }

    /**
     * Persist a coupon redemption against an order. Call inside the checkout transaction.
     */
    public function redeem(
        Coupon $coupon,
        Order $order,
        float $discount,
        ?User $user = null,
    ): CouponUsage {
        return CouponUsage::create([
            'coupon_id' => $coupon->id,
            'order_id' => $order->id,
            'user_id' => $user?->id ?? $order->user_id,
            'customer_email' => $order->customer_email,
            'discount_amount' => number_format($discount, 2, '.', ''),
        ]);
    }
}