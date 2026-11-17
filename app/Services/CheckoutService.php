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
        Log::info('Checkout: placeOrder START', [
            'cart_id' => $cart->id,
            'cart_user_id' => $cart->user_id ?? null,
            'customer_email' => $customer['email'] ?? null,
            'customer_phone' => $customer['phone'] ?? null,
            'customer_county' => $customer['county'] ?? null,
            'idempotency_key' => $customer['idempotency_key'] ?? null,
        ]);

        $result = DB::transaction(function () use ($cart, $customer) {
            Log::debug('Checkout: transaction opened, loading cart items');

            $items = $cart->items()->with('variation.product')->lockForUpdate()->get();

            Log::debug('Checkout: cart items loaded', [
                'cart_id' => $cart->id,
                'item_count' => $items->count(),
            ]);

            if ($items->isEmpty()) {
                Log::warning('Checkout: aborting — cart is empty', ['cart_id' => $cart->id]);
                throw new \InvalidArgumentException('Your cart is empty.');
            }

            $subtotal = 0.0;
            $snapshots = [];

            foreach ($items as $item) {
                $variation = ProductVariation::whereKey($item->variation_id)->lockForUpdate()->first();

                Log::debug('Checkout: processing line item', [
                    'cart_item_id' => $item->id,
                    'variation_id' => $item->variation_id,
                    'requested_qty' => $item->quantity,
                    'variation_found' => (bool) $variation,
                    'available_stock' => $variation?->stock_quantity,
                ]);

                if (! $variation || $variation->stock_quantity < $item->quantity) {
                    $name = $variation?->product?->name ?? 'a product';
                    Log::warning('Checkout: aborting — insufficient stock', [
                        'variation_id' => $item->variation_id,
                        'product_name' => $name,
                        'requested_qty' => $item->quantity,
                        'available_stock' => $variation?->stock_quantity ?? 0,
                    ]);
                    throw new \InvalidArgumentException(
                        "Insufficient stock for {$name}. Only ".($variation?->stock_quantity ?? 0).' available.'
                    );
                }

                $unitPrice = (float) ($variation->sale_price ?? $variation->price ?? $variation->product->price);
                $lineTotal = $unitPrice * $item->quantity;
                $subtotal += $lineTotal;

                Log::debug('Checkout: line item priced', [
                    'variation_id' => $variation->id,
                    'sku' => $variation->sku,
                    'unit_price' => $unitPrice,
                    'quantity' => $item->quantity,
                    'line_total' => $lineTotal,
                    'running_subtotal' => $subtotal,
                ]);

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

            Log::info('Checkout: subtotal computed', [
                'cart_id' => $cart->id,
                'subtotal' => $subtotal,
                'line_count' => count($snapshots),
            ]);

            // Coupon application (inside transaction, so usage count is accurate)
            $couponCode = $this->cart->getCouponCode();
            $discountTotal = 0.0;
            $coupon = null;

            Log::debug('Checkout: coupon lookup', [
                'coupon_code_from_cart' => $couponCode,
            ]);

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

                    Log::info('Checkout: coupon applied', [
                        'coupon_code' => $coupon?->code,
                        'discount_total' => $discountTotal,
                    ]);
                } catch (\InvalidArgumentException $e) {
                    // Coupon became invalid between cart and checkout — clear it silently
                    Log::warning('Checkout: coupon invalid at checkout — clearing silently', [
                        'coupon_code' => $couponCode,
                        'reason' => $e->getMessage(),
                    ]);
                    $this->cart->forgetCoupon();
                }
            } else {
                Log::debug('Checkout: no coupon in cart');
            }

            $shippingZone = ShippingZone::where('name', $customer['county'])
                ->where('is_active', true)
                ->first();

            Log::debug('Checkout: shipping zone lookup', [
                'county' => $customer['county'],
                'zone_found' => (bool) $shippingZone,
                'zone_id' => $shippingZone?->id,
            ]);

            if (! $shippingZone) {
                Log::warning('Checkout: aborting — shipping zone unavailable', [
                    'county' => $customer['county'],
                ]);
                throw new \InvalidArgumentException(
                    'Delivery to '.$customer['county'].' is not currently available.'
                );
            }

            $shippingFee = (float) $shippingZone->fee;
            $grandTotal = max(0, $subtotal - $discountTotal + $shippingFee);

            Log::info('Checkout: totals computed', [
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'shipping_fee' => $shippingFee,
                'grand_total' => $grandTotal,
                'coupon_code' => $coupon?->code,
            ]);

            $orderNumber = $this->generateOrderNumber();

            Log::debug('Checkout: creating order', [
                'order_number' => $orderNumber,
                'user_id' => auth()->id(),
            ]);

            $order = Order::create([
                'ulid' => (string) Str::ulid(),
                'order_number' => $orderNumber,
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

            Log::info('Checkout: order created', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'grand_total' => $order->grand_total,
            ]);

            foreach ($snapshots as $snapshot) {
                $order->items()->create($snapshot);
            }

            Log::debug('Checkout: order items written', [
                'order_id' => $order->id,
                'item_count' => count($snapshots),
            ]);

            // Record coupon usage
            if ($coupon) {
                $this->coupons->redeem($coupon, $order, $discountTotal, auth()->user());

                Log::info('Checkout: coupon redeemed', [
                    'order_id' => $order->id,
                    'coupon_code' => $coupon->code,
                    'discount_total' => $discountTotal,
                ]);
            }

            $order->recordStatus(null, 'pending', 'payment');
            $order->recordStatus(null, 'pending', 'fulfilment');

            Log::debug('Checkout: initial status history recorded', [
                'order_id' => $order->id,
            ]);

            $payment = Payment::create([
                'ulid' => (string) Str::ulid(),
                'order_id' => $order->id,
                'provider' => PaymentProvider::Mpesa,
                'amount' => $order->grand_total,
                'currency' => 'KES',
                'status' => PaymentStatus::Pending,
                'idempotency_key' => Str::uuid()->toString(),
            ]);

            Log::info('Checkout: payment record created', [
                'payment_id' => $payment->id,
                'order_id' => $order->id,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'provider' => $payment->provider->value,
            ]);

            // ----- STK Push -----
            Log::info('Checkout: initiating M-Pesa STK push', [
                'payment_id' => $payment->id,
                'order_number' => $order->order_number,
                'phone' => $customer['phone'],
                'amount' => $order->grand_total,
            ]);

            $stkResponse = $this->mpesa->initiate($payment, $customer['phone'], $order->order_number);

            Log::info('Checkout: M-Pesa STK raw response received', [
                'payment_id' => $payment->id,
                'order_number' => $order->order_number,
                'response_keys' => is_array($stkResponse) ? array_keys($stkResponse) : null,
                'response' => $stkResponse,
            ]);

            $checkoutRequestId = $stkResponse['CheckoutRequestID'] ?? null;

            if (! $checkoutRequestId) {
                Log::error('Checkout: M-Pesa did not return a CheckoutRequestID', [
                    'payment_id' => $payment->id,
                    'order_number' => $order->order_number,
                    'phone' => $customer['phone'],
                    'amount' => $order->grand_total,
                    'raw_response' => $stkResponse,
                    'response_code' => $stkResponse['ResponseCode'] ?? null,
                    'response_description' => $stkResponse['ResponseDescription'] ?? null,
                    'error_code' => $stkResponse['errorCode'] ?? null,
                    'error_message' => $stkResponse['errorMessage'] ?? null,
                ]);
                throw new \RuntimeException('M-Pesa did not return a CheckoutRequestID.');
            }

            Log::info('Checkout: M-Pesa STK accepted', [
                'payment_id' => $payment->id,
                'checkout_request_id' => $checkoutRequestId,
                'merchant_request_id' => $stkResponse['MerchantRequestID'] ?? null,
            ]);

            $payment->update([
                'checkout_request_id' => $checkoutRequestId,
                'merchant_request_id' => $stkResponse['MerchantRequestID'] ?? null,
                'status' => PaymentStatus::Processing,
                'raw_request' => ['phone' => $customer['phone'], 'amount' => $order->grand_total],
                'raw_response' => $stkResponse,
            ]);

            Log::debug('Checkout: payment updated to Processing', [
                'payment_id' => $payment->id,
                'checkout_request_id' => $checkoutRequestId,
            ]);

            PaymentAttempt::create([
                'payment_id' => $payment->id,
                'event' => 'initiate',
                'request_payload' => ['phone' => $customer['phone'], 'amount' => $order->grand_total],
                'response_payload' => $stkResponse,
                'status_code' => 200,
            ]);

            Log::debug('Checkout: PaymentAttempt recorded', [
                'payment_id' => $payment->id,
            ]);

            $this->cart->clear($cart);
            $this->cart->forgetCoupon();

            Log::debug('Checkout: cart cleared', [
                'cart_id' => $cart->id,
            ]);

            return ['order' => $order, 'payment' => $payment, 'stk_response' => $stkResponse];
        });

        Log::info('Checkout: transaction committed', [
            'order_id' => $result['order']->id,
            'order_number' => $result['order']->order_number,
            'payment_id' => $result['payment']->id,
        ]);

        try {
            Notification::route('mail', $result['order']->customer_email)
                ->notify(new OrderPlacedNotification($result['order']));

            Log::info('Checkout: OrderPlacedNotification sent', [
                'order_id' => $result['order']->id,
                'email' => $result['order']->customer_email,
            ]);
        } catch (\Throwable $e) {
            Log::warning('OrderPlacedNotification failed', [
                'order' => $result['order']->order_number,
                'error' => $e->getMessage(),
            ]);
        }

        Log::info('Checkout: placeOrder END', [
            'order_id' => $result['order']->id,
            'order_number' => $result['order']->order_number,
            'payment_id' => $result['payment']->id,
            'checkout_request_id' => $result['stk_response']['CheckoutRequestID'] ?? null,
        ]);

        return $result;
    }

    private function generateOrderNumber(): string
    {
        Log::debug('Checkout: generating order number');

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

        $number = sprintf('ORD-%d-%06d', $year, $next);

        Log::debug('Checkout: order number generated', [
            'year' => $year,
            'last_order_number' => $last?->order_number,
            'next_order_number' => $number,
        ]);

        return $number;
    }
}