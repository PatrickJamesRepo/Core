<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

class AdminManageEventsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Returns an admin user for testing.
     */
    protected function adminUser()
    {
        return User::factory()->create([
            'email' => 'admin@example.com',
            'roles' => ['admin'],
        ]);
    }

    /** @test */
    public function admin_can_access_events_index()
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get('/admin/manage-events');
        $response->assertStatus(200);
        // Adjust assertion based on the content of your index view.
        $response->assertSee('Manage Events');
    }

    /** @test */
    public function admin_can_access_event_create_page()
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get('/admin/manage-events/create');
        $response->assertStatus(200);
        // Adjust assertion based on your create page content.
        $response->assertSee('Create');
    }

    /** @test */
    public function admin_can_create_a_new_event()
    {
        $admin = $this->adminUser();

        $payload = [
            'name'                 => 'Test Event',
            'policyIds'            => "policy1\r\npolicy2", // Simulate newline-separated input.
            'endDateTime'          => Date::now()->addDay()->toDateString(),
            'startDateTime'        => Date::now()->toDateString(),
            'nonceValidForMinutes' => 10,
            // Include other fields as needed by your validation rules.
        ];

        $response = $this->actingAs($admin)->post('/admin/manage-events', $payload);
        $response->assertRedirect(route('manage-events.index'));
        $this->assertDatabaseHas('events', [
            'name' => 'Test Event',
        ]);
    }

    /** @test */
    public function admin_can_view_event_details()
    {
        $admin = $this->adminUser();
        $event = Event::factory()->create([
            'name' => 'Detailed Event',
        ]);

        $response = $this->actingAs($admin)->get("/admin/manage-events/{$event->id}");
        $response->assertStatus(200);
        $response->assertSee('Detailed Event');
    }

    /** @test */
    public function admin_can_access_event_edit_page()
    {
        $admin = $this->adminUser();
        $event = Event::factory()->create([
            'name' => 'Event To Edit',
        ]);

        $response = $this->actingAs($admin)->get("/admin/manage-events/{$event->id}/edit");
        $response->assertStatus(200);
        // Adjust the below assertion depending on what is displayed on the edit page.
        $response->assertSee('Edit');
    }

    /** @test */
    public function admin_can_update_an_event()
    {
        $admin = $this->adminUser();
        $event = Event::factory()->create([
            'name' => 'Old Event Name',
        ]);

        $payload = [
            'event_id'             => $event->id,
            'name'                 => 'Updated Event Name',
            'policyIds'            => "policy1\r\npolicy2",
            'endDateTime'          => Date::now()->addDays(2)->toDateString(),
            'startDateTime'        => Date::now()->toDateString(),
            'nonceValidForMinutes' => 15,
            // Include other fields as required.
        ];

        // Since the store method handles both creation and updates (depending on event_id presence)
        $response = $this->actingAs($admin)->post('/admin/manage-events', $payload);
        $response->assertRedirect(route('manage-events.index'));
        $this->assertDatabaseHas('events', [
            'id'   => $event->id,
            'name' => 'Updated Event Name',
        ]);
    }

    /** @test */
    public function non_admin_users_cannot_access_admin_manage_events()
    {
        $user = User::factory()->create([
            'roles' => ['user'], // A non-admin role.
        ]);

        $response = $this->actingAs($user)->get('/admin/manage-events');
        // Assuming unauthorized access returns a 403 error.
        $response->assertStatus(403);
    }
}
