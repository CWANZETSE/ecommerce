<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_redirected_from_customer_routes(): void
    {
        $this->get('/customer/orders')->assertRedirect('/login');
    }

    public function test_guest_redirected_from_admin_routes(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
    }

    public function test_customer_cannot_access_admin(): void
    {
        $customer = User::factory()->create(['role' => UserRole::Customer]);

        $this->actingAs($customer)->get('/admin/dashboard')->assertForbidden();
    }

    public function test_admin_redirected_from_customer_area(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/customer/orders')
            ->assertRedirect('/admin/dashboard');
    }

    public function test_customer_can_access_own_orders_only(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();

        $bobsOrder = \App\Models\Order::factory()->create(['user_id' => $bob->id]);

        $this->actingAs($alice)
            ->get("/customer/orders/{$bobsOrder->ulid}")
            ->assertForbidden();

        $this->actingAs($bob)
            ->get("/customer/orders/{$bobsOrder->ulid}")
            ->assertOk();
    }
}