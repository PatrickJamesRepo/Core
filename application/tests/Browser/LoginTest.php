<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('migrate:fresh', ['--env' => 'dusk.testing']);
        Artisan::call('db:seed', ['--env' => 'dusk.testing']);
    }

    /** @test */
    public function login_page_loads()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->screenshot('login-page-loads')
                ->assertSee('Login')
                ->assertPresent('form[action="' . route('login') . '"]')
                ->dump();
        });
    }

    /** @test */
    public function login_form_has_all_fields()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->assertPresent('input[name=email]')
                ->assertPresent('input[name=password]')
                ->assertPresent('input[name=remember]')
                ->assertPresent('input[name=_token]')
                ->assertPresent('button[type=submit]')
                ->screenshot('login-form-fields')
                ->dump();
        });
    }

    /** @test */
    public function user_can_login_successfully()
    {
        $user = User::factory()->create([
            'email' => 'duskuser@example.com',
            'password' => Hash::make('secret123'),
        ]);

        \Log::info('DUSK TEST USER CREATED', [
            'email' => $user->email,
            'password_check' => Hash::check('secret123', $user->password),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'secret123')
                ->screenshot('login-filled')
                ->press('Login')
                ->pause(1000)
                ->screenshot('login-after-submit')
                ->dump()
                ->assertPathIs('/dashboard')
                ->assertSee('You are logged in!');
        });
    }

    /** @test */
    public function user_can_logout_successfully()
    {
        $user = User::factory()->create([
            'email' => 'logoutuser@example.com',
            'password' => Hash::make('secret123'), // must hash
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'secret123')
                ->press('Login')
                ->waitForLocation('/dashboard') // 👈 not pause()
                ->assertPathIs('/dashboard')
                ->click('@navbarDropdown') // ensure this exists in blade
                ->clickLink('Logout')
                ->waitForLocation('/login')
                ->assertPathIs('/login')
                ->screenshot('logout-success');
        });
    }
}
