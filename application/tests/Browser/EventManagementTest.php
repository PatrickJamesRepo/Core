<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Event;
use App\Models\User;
use Tests\TestCase;

class EventManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_create_an_event()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $data = [
            'name' => 'Test Event',
            'location' => 'Test Location',
            'eventDate' => now()->toDateString(),
            'eventStart' => now()->format('H:i:s'),
            'eventEnd' => now()->addHours(2)->format('H:i:s'),
            'startDateTime' => now()->format('Y-m-d H:i:s'),
            'endDateTime' => now()->addDay()->format('Y-m-d H:i:s'),
            'hodlAsset' => 'asset123',
            'policyIds' => ['policy123', 'policy456'],
            'nonceValidForMinutes' => 30,
        ];

        $response = $this->post(route('admin.manage-events.store'), $data);

        $response->assertStatus(302);
        $this->assertDatabaseHas('events', ['name' => 'Test Event']);
    }

    /** @test */
    public function admin_can_edit_an_event()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $event = Event::factory()->create();

        $this->actingAs($admin);

        $updateData = [
            'name' => 'Updated Event',
            'location' => 'Updated Location',
            'eventDate' => now()->toDateString(),
            'eventStart' => now()->format('H:i:s'),
            'eventEnd' => now()->addHours(3)->format('H:i:s'),
            'startDateTime' => now()->format('Y-m-d H:i:s'),
            'endDateTime' => now()->addDay()->format('Y-m-d H:i:s'),
            'hodlAsset' => 'asset456',
            'policyIds' => ['policy789'],
            'nonceValidForMinutes' => 60,
        ];

        $response = $this->put(route('admin.manage-events.update', $event->id), $updateData);

        $response->assertStatus(302);
        $this->assertDatabaseHas('events', ['name' => 'Updated Event']);
    }

    /** @test */
    public function admin_can_delete_an_event()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $event = Event::factory()->create();

        $this->actingAs($admin);

        $response = $this->delete(route('admin.manage-events.destroy', $event->id));

        $response->assertStatus(302);
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    /** @test */
    public function validation_errors_display_correctly_when_creating_event()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $data = [
            // Missing required 'name' field
            'location' => 'Test',
            'eventDate' => now()->toDateString(),
            'eventStart' => now()->format('H:i:s'),
            'eventEnd' => now()->addHours(1)->format('H:i:s'),
            'startDateTime' => now()->format('Y-m-d H:i:s'),
            'endDateTime' => now()->addDay()->format('Y-m-d H:i:s'),
            'hodlAsset' => 'asset000',
            'policyIds' => ['policy000'],
            'nonceValidForMinutes' => 15,
        ];

        $response = $this->post(route('admin.manage-events.store'), $data);
        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function success_message_displays_after_event_creation()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $data = [
            'name' => 'Test Event',
            'location' => 'Test Location',
            'eventDate' => now()->toDateString(),
            'eventStart' => now()->format('H:i:s'),
            'eventEnd' => now()->addHours(2)->format('H:i:s'),
            'startDateTime' => now()->format('Y-m-d H:i:s'),
            'endDateTime' => now()->addDay()->format('Y-m-d H:i:s'),
            'hodlAsset' => 'asset123',
            'policyIds' => ['policy123'],
            'nonceValidForMinutes' => 30,
        ];

        $response = $this->post(route('admin.manage-events.store'), $data);
        $response->assertSessionHas('status', 'Event created');
    }
}
