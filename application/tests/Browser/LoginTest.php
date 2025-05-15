<?php
namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    public function test_user_can_login_successfully()
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create(['password' => bcrypt('password')]);

            $browser->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'password')
                ->press('Login')
                ->assertSee('Dashboard');
        });
    }

    public function test_user_can_logout_successfully()
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create(['password' => bcrypt('password')]);

            $browser->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'password')
                ->press('Login')
                ->assertSee('Dashboard')
                ->press('Logout')
                ->assertSee('Login');
        });
    }
}
