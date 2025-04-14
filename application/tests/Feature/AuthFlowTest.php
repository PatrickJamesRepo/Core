<?php

namespace Tests\Feature;

use App\Models\User;
use App\ThirdParty\CardanoClients\ICardanoClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Fakes\FakeCardanoClient;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Set up the test environment:
     * - Disable CSRF and email verification middleware.
     * - Bind the Cardano client interface to our fake implementation.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Disable CSRF and email verification middleware for the tests.
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->withoutMiddleware(\Illuminate\Auth\Middleware\EnsureEmailIsVerified::class);

        // Bind the ICardanoClient interface to our fake implementation.
        $this->app->bind(ICardanoClient::class, FakeCardanoClient::class);
    }

    /**
     * Test the complete registration flow including wallet connection fields.
     *
     * This test posts to /register with additional wallet fields. It asserts:
     * - A new user is created (including wallet_address).
     * - The response redirects to the expected URL.
     * - The user is authenticated.
     */
    public function test_user_can_register_with_wallet()
    {
        $userData = [
            'name'                  => 'Test User',
            'email'                 => 'testuser@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            // Wallet fields for web3 integration:
            'wallet_address'        => 'addr_test1qxxxxxxxxxxxx',
            'wallet_signature'      => 'fake-signature-123',
        ];

        // Send a POST request to /register.
        $response = $this->post('/register', $userData);

        // Assert that the user was created in the database, including wallet information.
        $this->assertDatabaseHas('users', [
            'email'         => 'testuser@example.com',
            'wallet_address'=> 'addr_test1qxxxxxxxxxxxx',
        ]);

        // Assert that the response redirects to '/home' (adjust based on your configuration).
        $response->assertRedirect('/home');

        // Verify that the user is authenticated.
        $this->assertAuthenticated();
    }

    /**
     * Test the login flow.
     *
     * Create a user with known credentials (wallet connection isn’t normally verified during login)
     * and then attempt to log in by posting those credentials to /login.
     */
    public function test_user_can_login()
    {
        $password = 'secret123';
        // Create a user with a known email and password.
        $user = User::factory()->create([
            'email'    => 'loginuser@example.com',
            'password' => Hash::make($password),
            // Optionally, if your login or user record requires wallet data, include it:
            'wallet_address' => 'addr_test1qxxxxxxxxxxxx',
        ]);

        // Send a POST request to /login with the known credentials.
        $response = $this->post('/login', [
            'email'    => 'loginuser@example.com',
            'password' => $password,
        ]);

        // Assert that the response redirects to '/home'. Adjust if your app uses a different redirect.
        $response->assertRedirect('/home');

        // Verify that the created user is now authenticated.
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test that login fails with invalid credentials.
     */
    public function test_user_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email'    => 'loginuser@example.com',
            'password' => Hash::make('secret123'),
        ]);

        // Attempt to log in with an incorrect password.
        $response = $this->post('/login', [
            'email'    => 'loginuser@example.com',
            'password' => 'wrongpassword',
        ]);

        // Assert redirection (typically back to the login page) and that session has error messages.
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

        // Send a POST request to /logout.
        $response = $this->post('/logout');

        // Assert that the response redirects to '/' (or another appropriate location).
        $response->assertRedirect('/');

        // Verify that the user is logged out.
        $this->assertGuest();
    }
}
