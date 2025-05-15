<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PasswordResetTest extends DuskTestCase
{
    public function test_user_can_request_password_reset_link()
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/password/reset')
                ->type('email', $user->email)
                ->press('Send Password Reset Link')
                ->assertSee('We have emailed your password reset link!');
        });
    }

    public function test_user_can_reset_password()
    {
        $user = User::factory()->create();
        $token = app('auth.password.broker')->createToken($user);

        $this->browse(function (Browser $browser) use ($user, $token) {
            $browser->visit(route('password.reset', ['token' => $token]))
                ->type('email', $user->email)
                ->type('password', 'newpassword')
                ->type('password_confirmation', 'newpassword')
                ->press('Reset Password')
                ->assertSee('Your password has been reset!');
        });
    }
}
