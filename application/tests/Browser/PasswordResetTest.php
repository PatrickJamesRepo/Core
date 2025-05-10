<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_user_can_request_password_reset_link()
    {
        $user = User::factory()->create();

        $response = $this->post(route('password.email'), ['email' => $user->email]);

        $response->assertStatus(302);  // Should redirect after request
        $response->assertSessionHas('status');  // Status should be in session
    }

    /** @test */
    public function test_user_can_reset_password()
    {
        $user = User::factory()->create();
        $token = app('auth.password.broker')->createToken($user);

        $response = $this->post(route('password.update'), [
            'email' => $user->email,
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
            'token' => $token,
        ]);

        $response->assertStatus(302);  // Redirect after successful reset
        $response->assertRedirect(route('login'));  // Should redirect to login
    }
}
