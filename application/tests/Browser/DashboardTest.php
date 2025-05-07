<?php
// tests/Browser/DashboardTest.php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class DashboardTest extends DuskTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate:fresh',    ['--env' => 'dusk.testing']);
        Artisan::call('db:seed',           ['--env' => 'dusk.testing']);
    }
    /**
     * @group skip
     */
    public function admin_sees_all_dashboard_components()
    {
        $user = User::factory()->create([
            'roles'    => ['admin', 'staff'],
            'password' => bcrypt('secret123'),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/dashboard')
                ->waitFor('.card-header', 5)            // Wait for dashboard header
                ->assertSeeIn('.card-header', 'Dashboard') // Make sure 'Dashboard' is in the header
                ->assertSeeIn('.card-body', 'You are logged in!') // Ensure logged-in message is present
                ->assertSeeLink('Manage Users')          // Check for 'Manage Users' link
                ->assertSeeLink('Manage Event')          // Check for 'Manage Event' link
                ->assertSeeLink('Scan Tickets')          // Check for 'Scan Tickets' link
                ->screenshot('dashboard-debug')         // Save screenshot for debugging
                ->dump();                               // Dump HTML for debugging
        });
    }

    /**
     * @group skip
     */
    public function staff_sees_only_staff_components()
    {
        $user = User::factory()->create([
            'roles'    => ['staff'],
            'password' => bcrypt('secret123'),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/dashboard')
                ->waitFor('.card-header', 5)
                ->assertDontSee('Manage Users')        // Ensure 'Manage Users' is not visible
                ->assertDontSee('Manage Event')        // Ensure 'Manage Event' is not visible
                ->assertSeeLink('Scan Tickets')        // Ensure 'Scan Tickets' is visible
                ->screenshot('staff-dashboard-debug')  // Save screenshot for debugging
                ->dump();                             // Dump HTML for debugging
        });
    }
}
