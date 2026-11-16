<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariation;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index(): Response
    {
        $cart = $this->cart->current();

        return Inertia::render('Storefront/Cart/Index', [
            'cart' => $cart ? $this->cart->serialize($cart) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'variation_id' => ['required', 'uuid', 'exists:product_variations,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $variation = ProductVariation::with('product')->findOrFail($data['variation_id']);

        try {
            $this->cart->add($variation, (int) $data['quantity']);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Added to cart.');
    }

    public function update(Request $request, CartItem $item): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $this->authorizeItem($item);

        try {
            $this->cart->updateQuantity($item, (int) $data['quantity']);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Cart updated.');
    }

    public function destroy(CartItem $item): RedirectResponse
    {
        $this->authorizeItem($item);
        $this->cart->remove($item);

        return back()->with('success', 'Item removed.');
    }

    private function authorizeItem(CartItem $item): void
    {
        $cart = $this->cart->current();
        abort_unless($cart && $item->cart_id === $cart->id, 403);
    }
}