<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Event;
use App\Models\User;

use Illuminate\Support\Str;
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
            'policyIds' => ['policy123'],
            'startDateTime' => now(),
            'endDateTime' => now()->addDays(1),
        ];

        $response = $this->post(route('admin.manage-events.store'), $data);

        $response->assertStatus(302); // Redirect after creation
        $this->assertDatabaseHas('events', ['name' => 'Test Event']);
    }

    /** @test */
    public function admin_can_edit_an_event()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $event = Event::factory()->create();

        $this->actingAs($admin);

        $newData = ['name' => 'Updated Event'];
        $response = $this->put(route('admin.manage-events.update', $event->id), $newData);

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
        $this->assertDeleted('events', ['id' => $event->id]);
    }

    /** @test */
    public function validation_errors_display_correctly_when_creating_event()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $data = [
            // Missing name
            'policyIds' => ['policy123'],
            'startDateTime' => now(),
            'endDateTime' => now()->addDays(1),
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
            'policyIds' => ['policy123'],
            'startDateTime' => now(),
            'endDateTime' => now()->addDays(1),
        ];

        $response = $this->post(route('admin.manage-events.store'), $data);

        $response->assertSessionHas('status', 'Event created');
    }
}
