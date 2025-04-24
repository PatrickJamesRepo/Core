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
    }

    /** @test */
    public function user_can_register_successfully()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->assertSee('Register') // make sure the form is there
                ->type('name', 'PCS Test User')
                ->type('email', 'register@pcs.example')
                ->type('password', 'secret123')
                ->type('password_confirmation', 'secret123')
                ->press('Register')
                ->pause(1000)
                ->assertPathIs('/dashboard');
        });
    }
}
