<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ShippingZone;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $cart = $this->cart->current();

        if (! $cart || $cart->items()->count() === 0) {
            return redirect()->route('cart_index')->with('error', 'Your cart is empty.');
        }

        $user = auth()->user();

        return Inertia::render('Storefront/Checkout/Index', [
            'cart' => $this->cart->serialize($cart),
            'counties' => ShippingZone::where('is_active', true)->orderBy('name')->get(['name', 'fee']),
            'user' => $user ? ['name' => $user->name, 'email' => $user->email] : null,
            'addresses' => $user
                ? $user->addresses()->orderByDesc('is_default')->orderBy('label')->get([
                    'id', 'label', 'recipient_name', 'phone', 'county', 'town', 'address', 'instructions', 'is_default',
                ])
                : [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
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

        $cart = $this->cart->current();

        if (! $cart) {
            return back()->with('error', 'Your cart is empty.');
        }

        try {
            $result = $this->checkout->placeOrder($cart, $data);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        // Save address to account if the user opted in
        $user = auth()->user();
        if ($user && ! empty($data['save_address'])) {
            $user->addresses()->create([
                'label' => $data['address_label'] ?? null,
                'recipient_name' => $data['name'],
                'phone' => $data['phone'],
                'county' => $data['county'],
                'town' => $data['town'],
                'address' => $data['address'],
                'instructions' => $data['instructions'] ?? null,
                'is_default' => $user->addresses()->count() === 0,
            ]);
        }

        return redirect()
            ->route('checkout_success', ['order' => $result['order']->ulid])
            ->with('success', 'Order placed. Complete the M-Pesa prompt on your phone.');
    }

    public function success(Order $order): Response
    {
        abort_unless($order->user_id === auth()->id() || $order->user_id === null, 403);

        $order->load(['items', 'payments']);

        return Inertia::render('Storefront/Checkout/Success', [
            'order' => [
                'ulid' => $order->ulid,
                'order_number' => $order->order_number,
                'grand_total' => $order->grand_total,
                'payment_status' => $order->payment_status->value,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'items' => $order->items->map(fn ($i) => [
                    'product_name' => $i->product_name,
                    'sku' => $i->sku,
                    'quantity' => $i->quantity,
                    'line_total' => $i->line_total,
                ])->values(),
            ],
        ]);
    }
}
