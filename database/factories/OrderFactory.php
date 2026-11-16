<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'ulid' => (string) Str::ulid(),
            'order_number' => 'ORD-TEST-' . strtoupper(Str::random(6)),
            'user_id' => User::factory(),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => '254712345678',
            'shipping_address' => [
                'county' => 'Nairobi City',
                'town' => 'Nairobi',
                'address' => fake()->streetAddress(),
                'instructions' => null,
            ],
            'subtotal' => '1000.00',
            'shipping_fee' => '0.00',
            'discount_total' => '0.00',
            'grand_total' => '1000.00',
            'payment_status' => 'pending',
            'fulfilment_status' => 'pending',
            'idempotency_key' => Str::uuid()->toString(),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);
    }
}