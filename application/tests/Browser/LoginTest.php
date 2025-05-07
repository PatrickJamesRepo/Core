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

        // Clear caches and migrate fresh
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('migrate:fresh', ['--env' => 'dusk.local']);
        Artisan::call('db:seed', ['--env' => 'dusk.local']);
    }

    /** @test */
    public function login_page_loads()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->assertSee('Login')
                ->assertPresent('form[action="' . route('login') . '"]');
        });
    }

    /** @test */
    public function login_form_has_all_fields()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->assertPresent('input[name="email"]')
                ->assertPresent('input[name="password"]')
                ->assertPresent('input[name="remember"]')
                ->assertPresent('input[name="_token"]')
                ->assertPresent('button[type="submit"]');
        });
    }

    /** @test */
    public function user_can_login_successfully()
    {
        // Create a test user with a hashed password
        $user = User::factory()->create([
            'email'    => 'duskuser@example.com',
            'password' => Hash::make('secret123'),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'secret123')
                ->press('Login')
                ->waitForLocation('/dashboard')
                ->assertPathIs('/dashboard')
                ->assertSee('You are logged in!');
        });
    }

    /** @test */
    public function user_can_logout_successfully()
    {
        $user = User::factory()->create([
            'email'    => 'logoutuser@example.com',
            'password' => Hash::make('secret123'),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'secret123')
                ->press('Login')
                ->waitForLocation('/dashboard')
                ->assertPathIs('/dashboard')
                // Open user menu and click logout
                ->click('#navbarDropdown')
                ->clickLink('Logout')
                ->waitForLocation('/login')
                ->assertPathIs('/login');
        });
    }
}
