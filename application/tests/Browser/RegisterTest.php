<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Illuminate\Support\Facades\Artisan;
use Tests\DuskTestCase;

class RegisterTest extends DuskTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate:fresh', ['--env' => 'dusk.testing']);
        Artisan::call('db:seed',    ['--env' => 'dusk.testing']);
    }

    /**
     * @group skip
     */
    public function user_can_register_successfully()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->assertSee('Register')
                ->assertPresent('input[name="name"]')
                ->assertPresent('input[name="email"]')
                ->assertPresent('input[name="password"]')
                ->assertPresent('input[name="password_confirmation"]')
                ->type('name', 'PCS Test User')
                ->type('email', 'register@pcs.example')
                ->type('password', 'secret123')
                ->type('password_confirmation', 'secret123')
                ->press('Register')
                ->waitForLocation('/dashboard')
                ->assertPathIs('/dashboard')
                ->assertSee('You are logged in!');
        });
    }
}
