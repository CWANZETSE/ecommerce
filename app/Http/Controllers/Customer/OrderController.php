<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(): Response
    {
        $orders = Order::where('user_id', auth()->id())
            ->withCount('items')
            ->orderByDesc('created_at')
            ->paginate(15);

        return Inertia::render('Customer/Orders/Index', [
            'orders' => $orders,
        ]);
    }

    public function show(Order $order): Response
    {
        // Ownership check — customers can only see their own orders
        abort_unless($order->user_id === auth()->id(), 403);

        $order->load(['items', 'payments']);

        // Determine which items are eligible for review (paid + delivered, not yet reviewed)
        $reviewedItemIds = \App\Models\ProductReview::where('user_id', auth()->id())
            ->whereIn('order_item_id', $order->items->pluck('id'))
            ->pluck('order_item_id')
            ->all();

        return Inertia::render('Customer/Orders/Show', [
            'order' => [
                'ulid' => $order->ulid,
                'order_number' => $order->order_number,
                'created_at' => $order->created_at->toDateTimeString(),
                'subtotal' => $order->subtotal,
                'shipping_fee' => $order->shipping_fee,
                'discount_total' => $order->discount_total,
                'coupon_code' => $order->coupon_code,
                'grand_total' => $order->grand_total,
                'payment_status' => $order->payment_status->value,
                'fulfilment_status' => $order->fulfilment_status->value,
                'shipping_address' => $order->shipping_address,
                'paid_at' => $order->paid_at?->toDateTimeString(),
                'items' => $order->items->map(fn ($i) => [
                    'id' => $i->id,
                    'product_name' => $i->product_name,
                    'sku' => $i->sku,
                    'attributes' => $i->attributes,
                    'unit_price' => $i->unit_price,
                    'quantity' => $i->quantity,
                    'line_total' => $i->line_total,
                    'variation_id' => $i->variation_id,
                    'can_review' => $order->payment_status->value === 'paid'
                        && $order->fulfilment_status->value === 'delivered'
                        && ! in_array($i->id, $reviewedItemIds, true)
                        && $i->variation_id !== null,
                    'variation_product_id' => $i->variation?->product_id,
                ])->values(),
                'payments' => $order->payments->map(fn ($p) => [
                    'provider' => $p->provider->value,
                    'amount' => $p->amount,
                    'status' => $p->status->value,
                    'provider_reference' => $p->provider_reference,
                    'paid_at' => $p->paid_at?->toDateTimeString(),
                ])->values(),
            ],
        ]);
    }
}