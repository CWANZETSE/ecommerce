<?php

namespace App\Http\Controllers\Storefront;

use App\Enums\OrderPaymentStatus;
use App\Enums\PaymentProvider;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ShippingZone;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\Payment\MpesaGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cart,
        private CheckoutService $checkout,
    ) {}

    public function index(): Response|RedirectResponse
    {
        Log::info('CheckoutController@index: START', [
            'user_id' => auth()->id(),
            'has_auth' => auth()->check(),
        ]);

        $cart = $this->cart->current();

        if (! $cart || $cart->items()->count() === 0) {
            Log::warning('CheckoutController@index: cart empty — redirecting to cart', [
                'cart_found' => (bool) $cart,
                'cart_id' => $cart?->id,
                'item_count' => $cart?->items()->count() ?? 0,
            ]);

            return redirect()->route('cart_index')->with('error', 'Your cart is empty.');
        }

        $user = auth()->user();

        Log::info('CheckoutController@index: rendering checkout page', [
            'cart_id' => $cart->id,
            'item_count' => $cart->items()->count(),
            'user_id' => $user?->id,
            'saved_addresses' => $user?->addresses()->count() ?? 0,
        ]);

        return Inertia::render('Storefront/Checkout/Index', [
            'cart' => $this->cart->serialize($cart),
            'counties' => ShippingZone::where('is_active', true)->orderBy('name')->get(['name', 'fee']),
            'user' => $user ? ['name' => $user->name, 'email' => $user->email, 'phone' => $user->phone] : null,
            'addresses' => $user
                ? $user->addresses()->orderByDesc('is_default')->orderBy('label')->get([
                    'id', 'label', 'recipient_name', 'phone', 'county', 'town', 'address', 'instructions', 'is_default',
                ])
                : [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Log::info('CheckoutController@store: START', [
            'user_id' => auth()->id(),
            'is_guest' => ! auth()->check(),
            'ip' => $request->ip(),
            'idempotency_key' => $request->input('idempotency_key'),
            'payload_keys' => array_keys($request->all()),
        ]);

        // ----- Validate -----
        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'phone' => ['required', 'string', 'max:20'],
                'county' => ['required', 'string', 'exists:shipping_zones,name'],
                'town' => ['required', 'string', 'max:255'],
                'address' => ['required', 'string', 'max:500'],
                'instructions' => ['nullable', 'string', 'max:500'],
                'idempotency_key' => ['required', 'string', 'max:64'],
                'save_address' => ['boolean'],
                'address_label' => ['nullable', 'string', 'max:50'],
            ]);

            Log::info('CheckoutController@store: validation passed', [
                'customer_name' => $data['name'],
                'customer_email' => $data['email'],
                'customer_phone' => $data['phone'],
                'county' => $data['county'],
                'save_address' => $data['save_address'] ?? false,
            ]);
        } catch (ValidationException $e) {
            Log::warning('CheckoutController@store: validation failed', [
                'errors' => $e->errors(),
            ]);
            throw $e;
        }

        // ----- Resolve cart -----
        $cart = $this->cart->current();

        if (! $cart) {
            Log::warning('CheckoutController@store: cart missing at store() — aborting');

            return back()->with('error', 'Your cart is empty.');
        }

        Log::info('CheckoutController@store: cart resolved', [
            'cart_id' => $cart->id,
            'cart_user_id' => $cart->user_id,
            'item_count' => $cart->items()->count(),
        ]);

        // ----- Place order (throws on business-rule failure) -----
        try {
            Log::info('CheckoutController@store: invoking CheckoutService::placeOrder');
            $result = $this->checkout->placeOrder($cart, $data);

            Log::info('CheckoutController@store: placeOrder returned successfully', [
                'order_id' => $result['order']->id,
                'order_ulid' => $result['order']->ulid,
                'order_number' => $result['order']->order_number,
                'payment_id' => $result['payment']->id,
                'payment_ulid' => $result['payment']->ulid,
                'payment_status' => $result['payment']->status->value,
                'checkout_request_id' => $result['stk_response']['checkoutRequestId'] ?? null,
            ]);
        } catch (\InvalidArgumentException $e) {
            Log::warning('CheckoutController@store: placeOrder business-rule failure', [
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('CheckoutController@store: placeOrder unexpected failure', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'We could not place your order. Please try again.');
        }

        // ----- Track the order for this session (used by waiting/status/success guards) -----
        session()->push('checkout_order_ulids', $result['order']->ulid);

        Log::debug('CheckoutController@store: order ulid stored in session', [
            'order_ulid' => $result['order']->ulid,
            'session_allowed_ulids' => session()->get('checkout_order_ulids', []),
        ]);

        // ----- Save address if the user opted in and is authenticated -----
        $user = auth()->user();
        if ($user && ! empty($data['save_address'])) {
            try {
                $address = $user->addresses()->create([
                    'label' => $data['address_label'] ?? null,
                    'recipient_name' => $data['name'],
                    'phone' => $data['phone'],
                    'county' => $data['county'],
                    'town' => $data['town'],
                    'address' => $data['address'],
                    'instructions' => $data['instructions'] ?? null,
                    'is_default' => $user->addresses()->count() === 0,
                ]);

                Log::info('CheckoutController@store: saved address to account', [
                    'user_id' => $user->id,
                    'address_id' => $address->id,
                    'is_default' => $address->is_default,
                ]);
            } catch (\Throwable $e) {
                // Address-save failure must not roll back the order — log and continue
                Log::warning('CheckoutController@store: address save failed (order unaffected)', [
                    'user_id' => $user->id,
                    'message' => $e->getMessage(),
                ]);
            }
        } else {
            Log::debug('CheckoutController@store: address not saved', [
                'has_user' => (bool) $user,
                'save_flag' => $data['save_address'] ?? false,
            ]);
        }

        // ----- Redirect to the waiting page (NOT the success page) -----
        $waitingUrl = route('checkout_waiting', ['order' => $result['order']->ulid]);

        Log::info('CheckoutController@store: redirecting to waiting page', [
            'order_ulid' => $result['order']->ulid,
            'order_number' => $result['order']->order_number,
            'waiting_url' => $waitingUrl,
            'payment_ulid_for_poll' => $result['payment']->ulid,
        ]);

        return redirect()
            ->to($waitingUrl)
            ->with('success', 'Payment prompt sent. Complete it on your phone.');
    }

    public function waiting(Order $order): Response|RedirectResponse
    {
        $allowedOrderUlids = session()->get('checkout_order_ulids', []);

        Log::info('CheckoutController@waiting: START', [
            'order_ulid' => $order->ulid,
            'order_number' => $order->order_number,
            'session_allowed_ulids' => $allowedOrderUlids,
            'is_allowed' => in_array($order->ulid, $allowedOrderUlids, true),
        ]);

        if (! in_array($order->ulid, $allowedOrderUlids, true)) {
            Log::warning('CheckoutController@waiting: order not in session — 403', [
                'order_ulid' => $order->ulid,
            ]);
            abort(403, 'This order is not available in your session.');
        }

        $payment = $order->payments()->latest()->first();

        Log::info('CheckoutController@waiting: rendering waiting page', [
            'order_ulid' => $order->ulid,
            'order_payment_status' => $order->payment_status->value,
            'order_fulfilment_status' => $order->fulfilment_status->value,
            'payment_id' => $payment?->id,
            'payment_ulid' => $payment?->ulid,
            'payment_status' => $payment?->status->value,
        ]);

        // Already paid? Go straight to success.
        if ($payment && $payment->status->value === 'succeeded') {
            Log::info('CheckoutController@waiting: payment already succeeded — redirecting to success', [
                'order_ulid' => $order->ulid,
            ]);

            return redirect()->route('checkout_success', ['order' => $order->ulid]);
        }

        return Inertia::render('Storefront/Checkout/Waiting', [
            'order' => [
                'ulid' => $order->ulid,
                'order_number' => $order->order_number,
                'grand_total' => $order->grand_total,
                'customer_phone' => $order->customer_phone,
            ],
            'payment_ulid' => $payment?->ulid,
            'initial_payment_status' => $payment?->status->value ?? 'pending',
        ]);
    }

    public function status(Payment $payment): JsonResponse
    {
        $order = $payment->order;
        $allowedOrderUlids = session()->get('checkout_order_ulids', []);

        $isAllowed = in_array($order->ulid, $allowedOrderUlids, true);

        Log::debug('CheckoutController@status: poll request', [
            'payment_ulid' => $payment->ulid,
            'order_ulid' => $order->ulid,
            'payment_status' => $payment->status->value,
            'order_payment_status' => $order->payment_status->value,
            'is_allowed_by_session' => $isAllowed,
            'is_admin' => auth()->user()?->isAdmin() ?? false,
            'user_id' => auth()->id(),
            'order_user_id' => $order->user_id,
        ]);

        // A legitimate owner is either: the session tracking this order (guest or logged-in),
        // or the authenticated user who placed the order, or an admin.
        $isOwnerByAuth = $order->user_id !== null && $order->user_id === auth()->id();

        if (! $isAllowed && ! $isOwnerByAuth) {
            Log::warning('CheckoutController@status: unauthorized poll attempt', [
                'payment_ulid' => $payment->ulid,
                'order_ulid' => $order->ulid,
            ]);
            abort(403, 'You are not allowed to view this payment.');
        }

        $paymentStatus = $payment->status->value;
        $orderPaymentStatus = $order->payment_status->value;

        return response()->json([
            'payment_status' => $paymentStatus,
            'order_payment_status' => $orderPaymentStatus,
            'order_ulid' => $order->ulid,
            'order_number' => $order->order_number,
            'paid' => $paymentStatus === 'succeeded',
            'failed' => in_array($paymentStatus, ['failed', 'cancelled'], true),
            'processing' => in_array($paymentStatus, ['pending', 'processing'], true),
            'redirect_to' => $paymentStatus === 'succeeded'
                ? route('checkout_success', ['order' => $order->ulid])
                : null,
        ]);
    }

    public function success(Order $order): Response|RedirectResponse
    {
        $allowedOrderUlids = session()->get('checkout_order_ulids', []);

        Log::info('CheckoutController@success: START', [
            'order_ulid' => $order->ulid,
            'order_number' => $order->order_number,
            'is_allowed_by_session' => in_array($order->ulid, $allowedOrderUlids, true),
            'user_id' => auth()->id(),
            'order_user_id' => $order->user_id,
        ]);

        $isAllowed = in_array($order->ulid, $allowedOrderUlids, true)
            || ($order->user_id !== null && $order->user_id === auth()->id());

        if (! $isAllowed) {
            Log::warning('CheckoutController@success: unauthorized access', [
                'order_ulid' => $order->ulid,
            ]);
            abort(403, 'This order is not available in your session.');
        }

        $payment = $order->payments()->latest()->first();

        if (! $payment || $payment->status->value !== 'succeeded') {
            Log::warning('CheckoutController@success: order not paid — redirecting to waiting', [
                'order_ulid' => $order->ulid,
                'payment_status' => $payment?->status->value,
            ]);

            return redirect()
                ->route('checkout_waiting', ['order' => $order->ulid])
                ->with('info', 'Please complete the payment before viewing your receipt.');
        }

        $order->load(['items', 'payments']);

        Log::info('CheckoutController@success: rendering receipt', [
            'order_ulid' => $order->ulid,
            'order_number' => $order->order_number,
            'paid_at' => $order->paid_at?->toDateTimeString(),
            'grand_total' => $order->grand_total,
            'payment_ulid' => $payment->ulid,
            'payment_reference' => $payment->provider_reference,
        ]);

        return Inertia::render('Storefront/Checkout/Success', [
            'order' => [
                'ulid' => $order->ulid,
                'order_number' => $order->order_number,
                'grand_total' => $order->grand_total,
                'payment_status' => $order->payment_status->value,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'paid_at' => $order->paid_at?->toDateTimeString(),
                'items' => $order->items->map(fn ($i) => [
                    'product_name' => $i->product_name,
                    'sku' => $i->sku,
                    'quantity' => $i->quantity,
                    'line_total' => $i->line_total,
                ])->values(),
            ],
        ]);
    }

    public function retry(Request $request, Order $order, MpesaGateway $mpesa)
    {
        // 1. Security check: ensure the user owns this order or it's a guest session
        $allowedOrderUlids = session()->get('checkout_order_ulids', []);
        $isOwner = in_array($order->ulid, $allowedOrderUlids, true)
                   || ($order->user_id && $order->user_id === auth()->id());

        if (! $isOwner) {
            abort(403, 'Unauthorized action.');
        }

        // 2. Prevent retrying an already paid order
        if ($order->payment_status === OrderPaymentStatus::Paid) {
            return redirect()->route('checkout_success', $order->ulid);
        }

        // 3. Create a new payment attempt (or reset the old one)
        try {
            $payment = DB::transaction(function () use ($order, $mpesa) {
                $payment = Payment::create([
                    'ulid' => (string) Str::ulid(),
                    'order_id' => $order->id,
                    'provider' => PaymentProvider::Mpesa,
                    'amount' => $order->grand_total,
                    'currency' => 'KES',
                    'status' => PaymentStatus::Pending,
                    'idempotency_key' => Str::uuid()->toString(),
                ]);

                // Trigger the STK push
                $stkResponse = $mpesa->initiate($payment, $order->customer_phone, $order->order_number);

                $checkoutRequestId = $stkResponse['checkoutRequestId'] ?? null;

                if (! $checkoutRequestId) {
                    throw new \RuntimeException('M-Pesa did not return a checkoutRequestId.');
                }

                $payment->update([
                    'checkout_request_id' => $checkoutRequestId,
                    'merchant_request_id' => $stkResponse['MerchantRequestID'] ?? null,
                    'status' => PaymentStatus::Processing,
                    'raw_request' => ['phone' => $order->customer_phone, 'amount' => $order->grand_total],
                    'raw_response' => $stkResponse,
                ]);

                return $payment;
            });

            return redirect()->route('checkout_waiting', $order->ulid)
                ->with('success', 'New payment prompt sent. Check your phone.');

        } catch (\Throwable $e) {
            Log::error('Retry STK push failed', ['order' => $order->ulid, 'error' => $e->getMessage()]);

            return back()->with('error', 'Could not initiate a new payment. Please try again later.');
        }
    }
}
