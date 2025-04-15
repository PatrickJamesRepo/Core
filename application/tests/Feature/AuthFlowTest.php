<?php

namespace Tests\Feature;

use App\Models\User;
use App\ThirdParty\CardanoClients\ICardanoClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fakes\FakeCardanoClient;
use Tests\Fakes\FakeUserService;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable middleware for testing.
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->withoutMiddleware(\Illuminate\Auth\Middleware\EnsureEmailIsVerified::class);

        // Bind external dependencies to fakes.
        $this->app->bind(ICardanoClient::class, FakeCardanoClient::class);
        $this->app->bind(\App\Services\UserService::class, FakeUserService::class);
    }

    /**
     * Test the registration flow without wallet fields.
     */
    public function test_user_can_register_without_wallet()
    {
        $userData = [
            'name'                  => 'Test User',
            'email'                 => 'testuser@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->post('/register', $userData);

        $this->assertDatabaseHas('users', [
            'email' => 'testuser@example.com',
        ]);

        // Adjust redirection as necessary.
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    /**
     * Test the login flow.
     */
    public function test_user_can_login()
    {
        $password = 'secret123';
        // Pass plain text password to allow the custom cast to hash it.
        $user = User::factory()->create([
            'email'    => 'loginuser@example.com',
            'password' => $password,
        ]);

        $response = $this->post('/login', [
            'email'    => 'loginuser@example.com',
            'password' => $password,
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test that login fails with invalid credentials.
     */
    public function test_user_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email'    => 'loginuser@example.com',
            'password' => 'secret123',
        ]);

        $response = $this->post('/login', [
            'email'    => 'loginuser@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    /**
     * Test the logout flow.
     */
    public function test_user_can_logout()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }
}
