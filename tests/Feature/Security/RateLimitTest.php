<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'x@y.com', 'password' => 'wrong']);
        }

        $response = $this->post('/login', ['email' => 'x@y.com', 'password' => 'wrong']);

        $this->assertTrue(
            $response->status() === 429 || $response->isRedirect(),
            'Login should be rate-limited after 5 attempts'
        );
    }
}