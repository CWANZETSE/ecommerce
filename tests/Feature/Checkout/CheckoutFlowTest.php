<?php

namespace Tests\Feature\Checkout;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariation;
use App\Models\ShippingZone;
use App\Services\CheckoutService;
use App\Services\Payment\MpesaGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    private function fakeMpesa(): void
    {
        $this->mock(MpesaGateway::class, function ($mock) {
            $mock->shouldReceive('initiate')
                ->andReturn([
                    'CheckoutRequestID' => 'ws_CO_TEST',
                    'MerchantRequestID' => 'mr_test',
                ]);
            $mock->shouldReceive('queryStatus')->andReturn(null);
        });
    }

    public function test_totals_are_recomputed_server_side(): void
    {
        $this->fakeMpesa();

        $variation = ProductVariation::factory()->create([
            'price' => 1000,
            'stock_quantity' => 5,
        ]);
        ShippingZone::create(['name' => 'Nairobi City', 'fee' => 200, 'is_active' => true]);

        $cart = Cart::create(['token' => 'test-token']);
        CartItem::create([
            'cart_id' => $cart->id,
            'variation_id' => $variation->id,
            'quantity' => 2,
        ]);

        $service = app(CheckoutService::class);
        $result = $service->placeOrder($cart, [
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'phone' => '254712345678',
            'county' => 'Nairobi City',
            'town' => 'Nairobi',
            'address' => '123 Test St',
            'instructions' => null,
            'idempotency_key' => 'test-key-1',
        ]);

        $this->assertSame('2000.00', $result['order']->subtotal);
        $this->assertSame('200.00', $result['order']->shipping_fee);
        $this->assertSame('2200.00', $result['order']->grand_total);
    }

    public function test_stock_not_deducted_until_callback(): void
    {
        $this->fakeMpesa();

        $variation = ProductVariation::factory()->create([
            'stock_quantity' => 5,
            'price' => 100,
        ]);
        ShippingZone::create(['name' => 'Nairobi City', 'fee' => 0, 'is_active' => true]);

        $cart = Cart::create(['token' => 'tok']);
        CartItem::create([
            'cart_id' => $cart->id,
            'variation_id' => $variation->id,
            'quantity' => 2,
        ]);

        app(CheckoutService::class)->placeOrder($cart, [
            'name' => 'T',
            'email' => 't@e.com',
            'phone' => '254712345678',
            'county' => 'Nairobi City',
            'town' => 'Nairobi',
            'address' => 'X',
            'instructions' => null,
            'idempotency_key' => 'k2',
        ]);

        // Stock must still be 5 after order placement — no callback yet
        $this->assertSame(5, $variation->fresh()->stock_quantity);
    }
}