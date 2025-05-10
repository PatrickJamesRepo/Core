<?php
namespace Tests\Browser;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_user_can_login_successfully()
    {
        $user = User::factory()->create();
        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(302); // Should redirect after successful login
        $response->assertRedirect(route('dashboard.index'));  // Check redirection to dashboard
    }

    /** @test */
    public function test_user_can_logout_successfully()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('logout'));

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function test_user_is_redirected_to_dashboard_on_login()
    {
        $user = User::factory()->create();
        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard.index'));
    }

    /** @test */
    public function test_guest_sees_login_and_register_links()
    {
        $response = $this->get(route('login'));

        $response->assertSee('Register');
        $response->assertSee('Login');
    }

    /** @test */
    public function test_authenticated_user_sees_dashboard_and_profile_links()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard.index'));

        $response->assertSee('Dashboard');
        $response->assertSee('Profile');
    }

    /** @test */
    public function test_login_form_validation()
    {
        $response = $this->post(route('login'), [
            'email' => '',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['email', 'password']);
    }
}
