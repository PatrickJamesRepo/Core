<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PasswordResetTest extends DuskTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate:fresh', ['--env' => 'dusk.testing']);
        Artisan::call('db:seed',    ['--env' => 'dusk.testing']);

        User::factory()->create([
            'email'    => 'reset@pcs.example',
            'password' => Hash::make('secret123'),
        ]);
    }

    /**
     * @group skip
     */
    public function user_can_reset_password()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/password/reset')
                ->assertSee('Reset Password')
                ->type('email', 'reset@pcs.example')
                ->press('Send Password Reset Link')
                ->assertSee('We have emailed your password reset link');

            $token = DB::table('password_resets')
                ->where('email', 'reset@pcs.example')
                ->first()->token;

            $browser->visit("/password/reset/{$token}")
                ->assertSee('Reset Password')
                ->assertPresent('input[name="email"]')
                ->assertPresent('input[name="password"]')
                ->assertPresent('input[name="password_confirmation"]')
                ->type('email', 'reset@pcs.example')
                ->type('password', 'newSecret123')
                ->type('password_confirmation', 'newSecret123')
                ->press('Reset Password')
                ->waitForLocation('/login')
                ->assertSee('Your password has been reset');

            $browser->visit('/login')
                ->type('email', 'reset@pcs.example')
                ->type('password', 'newSecret123')
                ->press('Login')
                ->waitForLocation('/dashboard')
                ->assertSee('You are logged in!');
        });
    }
}
