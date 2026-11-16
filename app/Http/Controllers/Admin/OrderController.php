<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderFulfilmentStatus;
use App\Enums\OrderPaymentStatus;
use App\Enums\StockMovementType;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Notifications\OrderCancelledNotification;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(private InventoryService $inventory) {}

    public function index(Request $request): Response
    {
        $orders = Order::query()
            ->with(['items'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('order_number', 'like', "%{$request->search}%")
                        ->orWhere('customer_name', 'like', "%{$request->search}%")
                        ->orWhere('customer_email', 'like', "%{$request->search}%")
                        ->orWhere('customer_phone', 'like', "%{$request->search}%");
                });
            })
            ->when($request->filled('payment_status'), fn ($q) => $q->where('payment_status', $request->payment_status))
            ->when($request->filled('fulfilment_status'), fn ($q) => $q->where('fulfilment_status', $request->fulfilment_status))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'filters' => $request->only(['search', 'payment_status', 'fulfilment_status']),
            'stats' => [
                'total' => Order::count(),
                'pending_payment' => Order::where('payment_status', 'pending')->count(),
                'paid' => Order::where('payment_status', 'paid')->count(),
                'processing' => Order::where('fulfilment_status', 'processing')->count(),
            ],
            'paymentStatuses' => array_map(fn ($s) => ['value' => $s->value, 'label' => ucfirst($s->value)], OrderPaymentStatus::cases()),
            'fulfilmentStatuses' => array_map(fn ($s) => ['value' => $s->value, 'label' => ucfirst($s->value)], OrderFulfilmentStatus::cases()),
        ]);
    }

    public function show(Order $order): Response
    {
        $order->load(['items', 'payments', 'statusHistory', 'user']);

        return Inertia::render('Admin/Orders/Show', [
            'order' => [
                'id' => $order->id,
                'ulid' => $order->ulid,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
                'customer_phone' => $order->customer_phone,
                'shipping_address' => $order->shipping_address,
                'subtotal' => $order->subtotal,
                'shipping_fee' => $order->shipping_fee,
                'grand_total' => $order->grand_total,
                'payment_status' => $order->payment_status->value,
                'fulfilment_status' => $order->fulfilment_status->value,
                'paid_at' => $order->paid_at?->toDateTimeString(),
                'cancelled_at' => $order->cancelled_at?->toDateTimeString(),
                'created_at' => $order->created_at->toDateTimeString(),
                'items' => $order->items->map(fn ($i) => [
                    'id' => $i->id,
                    'product_name' => $i->product_name,
                    'sku' => $i->sku,
                    'attributes' => $i->attributes,
                    'unit_price' => $i->unit_price,
                    'quantity' => $i->quantity,
                    'line_total' => $i->line_total,
                ])->values(),
                'payments' => $order->payments->map(fn ($p) => [
                    'id' => $p->id,
                    'ulid' => $p->ulid,
                    'provider' => $p->provider->value,
                    'amount' => $p->amount,
                    'status' => $p->status->value,
                    'provider_reference' => $p->provider_reference,
                    'checkout_request_id' => $p->checkout_request_id,
                    'failure_reason' => $p->failure_reason,
                    'paid_at' => $p->paid_at?->toDateTimeString(),
                    'created_at' => $p->created_at->toDateTimeString(),
                ])->values(),
                'history' => $order->statusHistory->map(fn ($h) => [
                    'id' => $h->id,
                    'from_status' => $h->from_status,
                    'to_status' => $h->to_status,
                    'type' => $h->type,
                    'note' => $h->note,
                    'created_at' => $h->created_at->toDateTimeString(),
                ])->values(),
            ],
            'fulfilmentStatuses' => array_map(fn ($s) => ['value' => $s->value, 'label' => ucfirst($s->value)], OrderFulfilmentStatus::cases()),
        ]);
    }

    public function updateFulfilment(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', array_column(OrderFulfilmentStatus::cases(), 'value'))],
            'note' => ['nullable', 'string', 'max:500'],
        ], [
            'status.required' => 'Choose a fulfilment status.',
            'status.in' => 'That status is not valid.',
        ]);

        $new = OrderFulfilmentStatus::from($data['status']);
        $previous = $order->fulfilment_status;

        if ($previous === $new) {
            return back()->with('info', 'Status unchanged.');
        }

        // Disallow moving to processing before payment confirmed
        if ($new !== OrderFulfilmentStatus::Pending
            && $order->payment_status !== OrderPaymentStatus::Paid
            && $new !== OrderFulfilmentStatus::Cancelled) {
            return back()->with('error', 'This order is not yet paid — cannot move to '.$new->value.'.');
        }

        $order->update(['fulfilment_status' => $new]);
        $order->recordStatus($previous->value, $new->value, 'fulfilment', $data['note'] ?? null);

        return back()->with('success', 'Fulfilment status updated.');
    }

    public function addNote(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'note' => ['required', 'string', 'max:1000'],
        ]);

        $order->recordStatus(
            $order->fulfilment_status->value,
            $order->fulfilment_status->value,
            'fulfilment',
            $data['note'],
        );

        return back()->with('success', 'Note added.');
    }

    public function cancel(Order $order): RedirectResponse
    {
        if ($order->fulfilment_status === OrderFulfilmentStatus::Delivered) {
            return back()->with('error', 'Delivered orders cannot be cancelled.');
        }

        if ($order->fulfilment_status === OrderFulfilmentStatus::Cancelled) {
            return back()->with('info', 'Order is already cancelled.');
        }

        $wasPaid = $order->payment_status === OrderPaymentStatus::Paid;

        // Restore stock if the order was paid
        if ($wasPaid) {
            foreach ($order->items as $item) {
                if ($item->variation) {
                    $this->inventory->adjust(
                        $item->variation,
                        $item->quantity,
                        StockMovementType::RefundReturn,
                        'Order cancelled',
                        $order,
                    );
                }
            }
        }

        $previous = $order->fulfilment_status;
        $order->update([
            'fulfilment_status' => OrderFulfilmentStatus::Cancelled,
            'cancelled_at' => now(),
        ]);
        $order->recordStatus($previous->value, 'cancelled', 'fulfilment');

        try {
            Notification::route('mail', $order->customer_email)
                ->notify(new OrderCancelledNotification($order, $wasPaid));
        } catch (\Throwable $e) {
            Log::warning('OrderCancelledNotification failed', [
                'order' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
        }

        return back()->with(
            'success',
            'Order cancelled'.($wasPaid ? ' and stock restored.' : '.'),
        );
    }
}
