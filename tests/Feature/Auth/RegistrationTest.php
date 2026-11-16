<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_users_can_register_as_customers(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect('/customer/account');

        $user = User::where('email', 'customer@example.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue(\Illuminate\Support\Str::isUuid($user->id));
        $this->assertSame(UserRole::Customer, $user->role);
        $this->assertFalse($user->isAdmin());
    }
}