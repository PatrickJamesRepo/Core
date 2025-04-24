<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use Tests\DuskTestCase;

class PasswordResetTest extends DuskTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate:fresh', ['--env' => 'dusk.testing']);
        User::factory()->create([
            'email' => 'reset@pcs.example',
            'password' => Hash::make('secret123'),
        ]);
    }

    /** @test */
    public function user_can_request_password_reset_link()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/password/reset')
                ->assertSee('Reset Password')
                ->type('email', 'reset@pcs.example')
                ->press('Send Password Reset Link')
                ->pause(1000)
                ->assertSee('We have emailed your password reset link');
        });
    }
}
