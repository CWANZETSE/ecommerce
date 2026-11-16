<?php

namespace App\Services;

use App\Enums\OrderFulfilmentStatus;
use App\Enums\OrderPaymentStatus;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentAttempt;
use App\Models\ProductVariation;
use App\Models\ShippingZone;
use App\Notifications\OrderPlacedNotification;
use App\Services\Payment\MpesaGateway;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class CheckoutService
{
    public function __construct(
        private CartService $cart,
        private MpesaGateway $mpesa,
        private CouponService $coupons,
    ) {}

    /**
     * Create the order + payment and initiate STK Push.
     *
     * @return array{order: Order, payment: Payment, stk_response: array}
     */
    public function placeOrder(Cart $cart, array $customer): array
    {
        $result = DB::transaction(function () use ($cart, $customer) {
            $items = $cart->items()->with('variation.product')->lockForUpdate()->get();
            if ($items->isEmpty()) {
                throw new \InvalidArgumentException('Your cart is empty.');
            }

            $subtotal = 0.0;
            $snapshots = [];

            foreach ($items as $item) {
                $variation = ProductVariation::whereKey($item->variation_id)->lockForUpdate()->first();
                if (! $variation || $variation->stock_quantity < $item->quantity) {
                    $name = $variation?->product?->name ?? 'a product';
                    throw new \InvalidArgumentException(
                        "Insufficient stock for {$name}. Only ".($variation?->stock_quantity ?? 0).' available.'
                    );
                }
                $unitPrice = (float) ($variation->sale_price ?? $variation->price ?? $variation->product->price);
                $lineTotal = $unitPrice * $item->quantity;
                $subtotal += $lineTotal;
                $snapshots[] = [
                    'variation_id' => $variation->id,
                    'product_name' => $variation->product->name,
                    'sku' => $variation->sku,
                    'attributes' => $variation->attributeValues->map(fn ($av) => [
                        'name' => $av->attribute->name,
                        'value' => $av->value,
                    ])->values()->all(),
                    'unit_price' => number_format($unitPrice, 2, '.', ''),
                    'quantity' => $item->quantity,
                    'line_total' => number_format($lineTotal, 2, '.', ''),
                ];
            }

            // Coupon application (inside transaction, so usage count is accurate)
            $couponCode = $this->cart->getCouponCode();
            $discountTotal = 0.0;
            $coupon = null;

            if ($couponCode) {
                try {
                    $result = $this->coupons->validate(
                        $couponCode,
                        $subtotal,
                        $customer['email'],
                        auth()->user(),
                    );
                    $coupon = $result['coupon'];
                    $discountTotal = $result['discount'];
                } catch (\InvalidArgumentException $e) {
                    // Coupon became invalid between cart and checkout — clear it silently
                    $this->cart->forgetCoupon();
                }
            }

            $shippingZone = ShippingZone::where('name', $customer['county'])
                ->where('is_active', true)
                ->first();

            if (! $shippingZone) {
                throw new \InvalidArgumentException(
                    'Delivery to '.$customer['county'].' is not currently available.'
                );
            }

            $shippingFee = (float) $shippingZone->fee;
            $grandTotal = max(0, $subtotal - $discountTotal + $shippingFee);

            $order = Order::create([
                'ulid' => (string) Str::ulid(),
                'order_number' => $this->generateOrderNumber(),
                'user_id' => auth()->id(),
                'customer_name' => $customer['name'],
                'customer_email' => $customer['email'],
                'customer_phone' => $customer['phone'],
                'shipping_address' => [
                    'county' => $customer['county'],
                    'town' => $customer['town'],
                    'address' => $customer['address'],
                    'instructions' => $customer['instructions'] ?? null,
                ],
                'subtotal' => number_format($subtotal, 2, '.', ''),
                'shipping_fee' => number_format($shippingFee, 2, '.', ''),
                'coupon_code' => $coupon?->code,
                'discount_total' => number_format($discountTotal, 2, '.', ''),
                'grand_total' => number_format($grandTotal, 2, '.', ''),
                'payment_status' => OrderPaymentStatus::Pending,
                'fulfilment_status' => OrderFulfilmentStatus::Pending,
                'idempotency_key' => $customer['idempotency_key'],
            ]);

            foreach ($snapshots as $snapshot) {
                $order->items()->create($snapshot);
            }

            // Record coupon usage
            if ($coupon) {
                $this->coupons->redeem($coupon, $order, $discountTotal, auth()->user());
            }

            $order->recordStatus(null, 'pending', 'payment');
            $order->recordStatus(null, 'pending', 'fulfilment');

            $payment = Payment::create([
                'ulid' => (string) Str::ulid(),
                'order_id' => $order->id,
                'provider' => PaymentProvider::Mpesa,
                'amount' => $order->grand_total,
                'currency' => 'KES',
                'status' => PaymentStatus::Pending,
                'idempotency_key' => Str::uuid()->toString(),
            ]);

            $stkResponse = $this->mpesa->initiate($payment, $customer['phone'], $order->order_number);
            $checkoutRequestId = $stkResponse['CheckoutRequestID'] ?? null;
            if (! $checkoutRequestId) {
                throw new \RuntimeException('M-Pesa did not return a CheckoutRequestID.');
            }

            $payment->update([
                'checkout_request_id' => $checkoutRequestId,
                'merchant_request_id' => $stkResponse['MerchantRequestID'] ?? null,
                'status' => PaymentStatus::Processing,
                'raw_request' => ['phone' => $customer['phone'], 'amount' => $order->grand_total],
                'raw_response' => $stkResponse,
            ]);

            PaymentAttempt::create([
                'payment_id' => $payment->id,
                'event' => 'initiate',
                'request_payload' => ['phone' => $customer['phone'], 'amount' => $order->grand_total],
                'response_payload' => $stkResponse,
                'status_code' => 200,
            ]);

            $this->cart->clear($cart);
            $this->cart->forgetCoupon();

            return ['order' => $order, 'payment' => $payment, 'stk_response' => $stkResponse];
        });

        try {
            Notification::route('mail', $result['order']->customer_email)
                ->notify(new OrderPlacedNotification($result['order']));
        } catch (\Throwable $e) {
            Log::warning('OrderPlacedNotification failed', [
                'order' => $result['order']->order_number,
                'error' => $e->getMessage(),
            ]);
        }

        return $result;
    }

    private function generateOrderNumber(): string
    {
        $year = now()->year;
        $last = Order::whereYear('created_at', $year)
            ->lockForUpdate()
            ->orderByDesc('order_number')
            ->first();

        $next = 1;
        if ($last) {
            $parts = explode('-', $last->order_number);
            $next = ((int) end($parts)) + 1;
        }

        return sprintf('ORD-%d-%06d', $year, $next);
    }
}
