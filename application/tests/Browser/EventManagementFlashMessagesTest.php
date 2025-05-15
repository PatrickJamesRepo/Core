<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Event;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class EventManagementFlashMessagesTest extends DuskTestCase
{
    public function test_event_creation_flash_message()
    {
        $response = $this->post(route('manage-events.store'), [
            'name' => 'Test Event',
            'startDateTime' => now(),
            'endDateTime' => now()->addHours(1),
            'policyIds' => ['policy_id_1'],
            'nonceValidForMinutes' => 10,
            'hodlAsset' => true,
        ]);

        $response->assertRedirect(route('admin.manage-events.index'));
        $response->assertSessionHas('status', __('Event created'));
    }

    public function test_event_update_flash_message()
    {
        $event = Event::factory()->create();

        $response = $this->put(route('manage-events.update', $event), [
            'name' => 'Updated Test Event',
            'startDateTime' => now(),
            'endDateTime' => now()->addHours(2),
            'policyIds' => ['policy_id_1'],
            'nonceValidForMinutes' => 10,
            'hodlAsset' => false,
        ]);

        $response->assertRedirect(route('admin.manage-events.index'));
        $response->assertSessionHas('status', __('Event updated'));
    }

    public function test_event_deletion_flash_message()
    {
        $event = Event::factory()->create();

        $response = $this->delete(route('manage-events.destroy', $event));

        $response->assertRedirect(route('admin.manage-events.index'));
        $response->assertSessionHas('status', __('Event deleted'));
    }

    public function test_event_creation_validation_error()
    {
        $response = $this->post(route('manage-events.store'), [
            'name' => '', // Invalid name
            'startDateTime' => now(),
            'endDateTime' => now()->addHours(1),
            'policyIds' => ['policy_id_1'],
            'nonceValidForMinutes' => 10,
        ]);

        $response->assertSessionHasErrors('name');  // Check if 'name' error exists
        $response->assertStatus(302);  // Ensure the response is a redirect
    }


}
