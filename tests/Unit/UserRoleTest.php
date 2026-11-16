<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_role_is_cast_to_enum(): void
    {
        $user = User::factory()->create(['role' => UserRole::Admin]);

        $this->assertInstanceOf(UserRole::class, $user->role);
        $this->assertSame(UserRole::Admin, $user->role);
    }

    public function test_is_admin_helper(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $customer = User::factory()->create(['role' => UserRole::Customer]);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($customer->isAdmin());
    }
}