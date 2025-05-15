<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class DashboardTest extends DuskTestCase
{
    public function test_admin_dashboard_sees_manage_links()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->visit('/login')
                ->type('email', $admin->email)
                ->type('password', 'password')  // Ensure the password is correct
                ->press('Login')
                ->assertSee('Manage Users')
                ->assertSee('Manage Events');
        });
    }

    public function test_staff_dashboard_sees_only_scan_tickets()
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->browse(function (Browser $browser) use ($staff) {
            $browser->visit('/login')
                ->type('email', $staff->email)
                ->type('password', 'password')
                ->press('Login')
                ->assertSee('Scan Tickets')
                ->assertDontSee('Manage Users')
                ->assertDontSee('Manage Events');
        });
    }
}
