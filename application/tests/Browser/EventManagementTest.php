<?php
namespace Tests\Browser;

use App\Models\User;
use App\Models\Event;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class EventManagementTest extends DuskTestCase
{
    public function test_admin_can_create_event()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->visit('/login')
                ->type('email', $admin->email)
                ->type('password', 'password')
                ->press('Login')
                ->visit('/admin/manage-events/create')
                ->type('name', 'New Event')
                ->type('location', 'Event Location')
                ->type('eventDate', now()->toDateString()) // Add event date
                ->type('eventStart', now()->addHours(1)->toTimeString()) // Start time
                ->type('eventEnd', now()->addHours(2)->toTimeString()) // End time
                ->type('startDateTime', now()->toDateTimeString()) // Add startDateTime
                ->type('endDateTime', now()->addDay()->toDateTimeString()) // Add endDateTime
                ->type('nonceValidForMinutes', 30)
                ->press('Create Event')
                ->waitForText('Event Created') // Add waiting for the success text
                ->assertSee('Event Created');
        });
    }

    public function test_admin_can_edit_event()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $event = Event::factory()->create();

        $this->browse(function (Browser $browser) use ($admin, $event) {
            $browser->visit('/login')
                ->type('email', $admin->email)
                ->type('password', 'password')
                ->press('Login')
                ->visit("/admin/manage-events/{$event->id}/edit")
                ->assertSee('Edit Event')
                ->type('name', 'Updated Event')
                ->press('Update Event')
                ->waitForText('Event updated') // Verify successful update
                ->assertSee('Event updated');
        });
    }

    public function test_admin_can_delete_event()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $event = Event::factory()->create();

        $this->browse(function (Browser $browser) use ($admin, $event) {
            $browser->visit('/login')
                ->type('email', $admin->email)
                ->type('password', 'password')
                ->press('Login')
                ->visit("/admin/manage-events/{$event->id}/delete")
                ->press('Delete Event')
                ->waitForText('Event deleted') // Verify successful deletion
                ->assertSee('Event deleted');
        });
    }


    public function test_event_ticket_generation_is_optional()
    {
        $response = $this->post(route('manage-events.store'), [
            'name' => 'Test Event without Tickets',
            'startDateTime' => now(),
            'endDateTime' => now()->addHours(1),
            'policyIds' => ['policy_id_1'],
            'nonceValidForMinutes' => 10,
            'hodlAsset' => false,  // No ticket generation
        ]);

        $event = Event::where('name', 'Test Event without Tickets')->first();
        $this->assertNotNull($event);
        $this->assertEquals(0, $event->tickets()->count());  // Ensure no tickets are generated
    }

}
