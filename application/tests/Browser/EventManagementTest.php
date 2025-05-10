<?php
namespace Tests\Browser;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Event;
use App\Models\User;
use Tests\TestCase;

class EventManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_admin_can_create_event()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $eventData = [
            'name' => 'New Event',
            'location' => 'Event Location',
            'eventDate' => now()->toDateString(),
            'eventStart' => now()->format('H:i:s'),
            'eventEnd' => now()->addHours(2)->format('H:i:s'),
            'startDateTime' => now()->format('Y-m-d H:i:s'),
            'endDateTime' => now()->addDay()->format('Y-m-d H:i:s'),
            'hodlAsset' => true,
            'policyIds' => ['policy123', 'policy456'],
            'nonceValidForMinutes' => 30,
        ];

        $response = $this->post(route('admin.manage-events.store'), $eventData);

        $response->assertStatus(302);  // Redirect to event list
        $this->assertDatabaseHas('events', ['name' => 'New Event']);
    }

    /** @test */
    public function test_event_creation_fails_with_missing_location()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $data = [
            'name' => 'Event Without Location',
            'eventDate' => now()->toDateString(),
            'eventStart' => now()->format('H:i:s'),
            'eventEnd' => now()->addHours(2)->format('H:i:s'),
            'startDateTime' => now()->format('Y-m-d H:i:s'),
            'endDateTime' => now()->addDay()->format('Y-m-d H:i:s'),
            'hodlAsset' => true,
            'policyIds' => ['policy123', 'policy456'],
            'nonceValidForMinutes' => 30,
        ];

        $response = $this->post(route('admin.manage-events.store'), $data);

        $response->assertSessionHasErrors('location');
    }

    /** @test */
    public function test_admin_can_update_event()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $event = Event::factory()->create();
        $this->actingAs($admin);

        $data = [
            'name' => 'Updated Event',
            'location' => 'Updated Location',
            'eventDate' => now()->toDateString(),
            'eventStart' => now()->format('H:i:s'),
            'eventEnd' => now()->addHours(3)->format('H:i:s'),
            'startDateTime' => now()->format('Y-m-d H:i:s'),
            'endDateTime' => now()->addDay()->format('Y-m-d H:i:s'),
            'hodlAsset' => true,
            'policyIds' => ['policy123'],
            'nonceValidForMinutes' => 30,
        ];

        $response = $this->put(route('admin.manage-events.update', $event->id), $data);

        $response->assertStatus(302); // Redirect
        $this->assertDatabaseHas('events', ['name' => 'Updated Event']);
    }

    /** @test */
    public function test_admin_can_delete_event()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $event = Event::factory()->create();
        $this->actingAs($admin);

        $response = $this->delete(route('admin.manage-events.destroy', $event->id));

        $response->assertStatus(302); // Redirect
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }
}
