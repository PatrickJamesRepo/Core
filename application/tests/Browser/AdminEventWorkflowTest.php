<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Event;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AdminEventWorkflowTest extends DuskTestCase
{
    public function test_admin_event_workflow()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('adminpassword'),
            'role' => 'admin',
        ]);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->visit('/login')
                ->type('email', $admin->email)
                ->type('password', 'adminpassword')
                ->press('Login')
                ->assertSee('Dashboard');

            // Create event
            $browser->visit('/admin/manage-events/create')
                ->assertSee('Create New Event')
                ->type('name', 'New Event')
                ->type('startDateTime', now()->toDateString())
                ->type('endDateTime', now()->addDay()->toDateString())
                ->press('Create Event')
                ->assertSee('Event Created');

            // Check if event appears
            $browser->visit('/admin/manage-events')
                ->assertSee('New Event')
                ->assertSee(now()->toDateString())
                ->assertSee(now()->addDay()->toDateString());
        });
    }
}
