<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_admin(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_customers_cannot_access_admin(): void
    {
        $customer = User::factory()->create(['role' => UserRole::Customer]);

        $this->actingAs($customer)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_admins_can_access_admin(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk();
    }
}