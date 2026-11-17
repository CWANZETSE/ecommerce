<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariation;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CartService
{
    public const COOKIE_NAME = 'cart_token';

    public const COOKIE_MINUTES = 60 * 24 * 30; // 30 days

    public const MAX_QTY_PER_LINE = 99;

    public function current(): ?Cart
{
    $relations = [
        'items.variation.product.primaryImage',
        'items.variation.attributeValues.attribute',
    ];

    $userId = auth()->id();

    if ($userId) {
        return Cart::with($relations)
            ->where('user_id', $userId)
            ->first();
    }

    $token = Cookie::get(self::COOKIE_NAME);
    if (! $token) {
        return null;
    }

    return Cart::with($relations)
        ->where('token', $token)
        ->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        })
        ->first();
}

    /**
     * Return the current cart or create one (and set the guest cookie).
     */
    public function getOrCreate(): Cart
    {
        $cart = $this->current();
        if ($cart) {
            return $cart;
        }

        $userId = auth()->id();
        if ($userId) {
            return Cart::create(['user_id' => $userId]);
        }

        $token = (string) Str::uuid();
        $cart = Cart::create([
            'token' => $token,
            'expires_at' => now()->addDays(30),
        ]);

        Cookie::queue(self::COOKIE_NAME, $token, self::COOKIE_MINUTES);

        return $cart;
    }

    public function add(ProductVariation $variation, int $qty): CartItem
    {
        if ($qty < 1 || $qty > self::MAX_QTY_PER_LINE) {
            throw new InvalidArgumentException('Quantity must be between 1 and '.self::MAX_QTY_PER_LINE.'.');
        }

        if ($variation->stock_quantity < $qty) {
            throw new InvalidArgumentException("Only {$variation->stock_quantity} available.");
        }

        return DB::transaction(function () use ($variation, $qty) {
            $cart = $this->getOrCreate();

            $item = CartItem::where('cart_id', $cart->id)
                ->where('variation_id', $variation->id)
                ->lockForUpdate()
                ->first();

            $newQty = ($item?->quantity ?? 0) + $qty;
            if ($newQty > self::MAX_QTY_PER_LINE) {
                throw new InvalidArgumentException('You can only add up to '.self::MAX_QTY_PER_LINE.' of this item.');
            }
            if ($newQty > $variation->stock_quantity) {
                throw new InvalidArgumentException("Only {$variation->stock_quantity} available.");
            }

            if ($item) {
                $item->update(['quantity' => $newQty]);

                return $item;
            }

            return CartItem::create([
                'cart_id' => $cart->id,
                'variation_id' => $variation->id,
                'quantity' => $qty,
            ]);
        });
    }

    public function updateQuantity(CartItem $item, int $qty): CartItem
    {
        if ($qty < 1 || $qty > self::MAX_QTY_PER_LINE) {
            throw new InvalidArgumentException('Quantity must be between 1 and '.self::MAX_QTY_PER_LINE.'.');
        }

        if ($qty > $item->variation->stock_quantity) {
            throw new InvalidArgumentException("Only {$item->variation->stock_quantity} available.");
        }

        $item->update(['quantity' => $qty]);

        return $item;
    }

    public function remove(CartItem $item): void
    {
        $item->delete();
    }

    public function clear(Cart $cart): void
    {
        $cart->items()->delete();
    }

    /**
     * After login: merge the guest cart into the user's cart, then delete the guest cart. Fortify fires Login and Authenticated events. Hook the merge on the Login event, right after successful authentication. Add to app/Providers/FortifyServiceProvider.php boot():
     */
    public function mergeGuestCartIntoUser(string $userId): void
    {
        $token = Cookie::get(self::COOKIE_NAME);
        if (! $token) {
            return;
        }

        DB::transaction(function () use ($token, $userId) {
            $guest = Cart::where('token', $token)->whereNull('user_id')->lockForUpdate()->first();
            if (! $guest) {
                return;
            }

            $userCart = Cart::where('user_id', $userId)->lockForUpdate()->first();
            if (! $userCart) {
                // Adopt the guest cart
                $guest->update(['user_id' => $userId, 'token' => null, 'expires_at' => null]);
                Cookie::queue(Cookie::forget(self::COOKIE_NAME));

                return;
            }

            // Merge items
            foreach ($guest->items as $guestItem) {
                $existing = CartItem::where('cart_id', $userCart->id)
                    ->where('variation_id', $guestItem->variation_id)
                    ->first();

                if ($existing) {
                    $newQty = min(
                        self::MAX_QTY_PER_LINE,
                        $existing->quantity + $guestItem->quantity,
                        $guestItem->variation->stock_quantity,
                    );
                    $existing->update(['quantity' => $newQty]);
                } else {
                    $qty = min($guestItem->quantity, $guestItem->variation->stock_quantity);
                    if ($qty > 0) {
                        CartItem::create([
                            'cart_id' => $userCart->id,
                            'variation_id' => $guestItem->variation_id,
                            'quantity' => $qty,
                        ]);
                    }
                }
            }

            $guest->items()->delete();
            $guest->delete();
            Cookie::queue(Cookie::forget(self::COOKIE_NAME));
        });
    }

    public function count(Cart $cart): int
    {
        return (int) $cart->items()->sum('quantity');
    }

    /**
     * Shape for the cart drawer / cart page.
     */
    public function serialize(Cart $cart): array
    {
        $items = $cart->items->map(function (CartItem $item) {
            $variation = $item->variation;
            $product = $variation->product;

            $unitPrice = (float) ($variation->sale_price ?? $variation->price ?? $product->price);
            $lineTotal = $unitPrice * $item->quantity;

            return [
                'id' => $item->id,
                'quantity' => $item->quantity,
                'variation_id' => $variation->id,
                'sku' => $variation->sku,
                'unit_price' => number_format($unitPrice, 2, '.', ''),
                'line_total' => number_format($lineTotal, 2, '.', ''),
                'stock_quantity' => $variation->stock_quantity,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'primary_image' => $product->primaryImage
                    ? ['path' =>$product->primary_image_url,]
                    : null,
                ],
                'attributes' => $variation->attributeValues->map(fn ($av) => [
                    'name' => $av->attribute->name,
                    'value' => $av->value,
                ])->values()->all(),
            ];
        })->values();

        $subtotal = $items->sum(fn ($i) => (float) $i['line_total']);

        $couponCode = $this->getCouponCode();
        $discount = 0.0;
        $couponError = null;
        if ($couponCode) {
            try {
                $result = app(CouponService::class)->validate(
                    $couponCode,
                    $subtotal,
                    auth()->user()?->email,
                    auth()->user(),
                );
                $discount = $result['discount'];
            } catch (InvalidArgumentException $e) {
                $couponError = $e->getMessage();
                $this->forgetCoupon();
                $couponCode = null;
            }
        }

        return [
            'id' => $cart->id,
            'items' => $items->all(),
            'item_count' => $items->sum('quantity'),
            'subtotal' => number_format($subtotal, 2, '.', ''),
            'discount' => number_format($discount, 2, '.', ''),
            'coupon_code' => $couponCode,
            'coupon_error' => $couponError,
            'currency' => 'KES',
        ];
    }

    public function setCoupon(?string $code): void
    {
        if ($code === null || $code === '') {
            session()->forget('applied_coupon');

            return;
        }
        session(['applied_coupon' => strtoupper(trim($code))]);
    }

    public function getCouponCode(): ?string
    {
        return session('applied_coupon');
    }

    public function forgetCoupon(): void
    {
        session()->forget('applied_coupon');
    }
}
