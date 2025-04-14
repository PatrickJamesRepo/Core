<?php

namespace Tests\Unit\Services;

use App\Exceptions\AppException;
use App\Models\Event;
use App\Services\EventService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that getEventList returns expected attributes.
     */
    public function test_get_event_list_returns_expected_columns()
    {
        // Arrange: Create an event with test data.
        Event::factory()->create([
            'name' => 'Test Event',
        ]);

        $service = new EventService();
        $events = $service->getEventList();

        // Assert that events exist and have the expected attributes.
        $this->assertNotEmpty($events);
        foreach ($events as $event) {
            // Instead of checking for public properties, assert they return non-null values.
            $this->assertNotNull($event->uuid, 'Event UUID is null');
            $this->assertNotNull($event->name, 'Event name is null');
            $this->assertNotNull($event->policyIds, 'Event policyIds is null');
        }
    }

    /**
     * Test creating a new event.
     */
    public function test_save_creates_new_event()
    {
        $payload = [
            'name'                 => 'New Event',
            'policyIds'            => "policy1\npolicy2", // Newline separated policy IDs.
            'endDateTime'          => '2025-12-31 23:59:59',
            'nonceValidForMinutes' => 10,
            // Add any additional fields required by validation.
        ];

        $service = new EventService();
        $service->save($payload);

        // Assert that the event is created in the database.
        $this->assertDatabaseHas('events', ['name' => 'New Event']);
    }

    /**
     * Test saving an event with invalid data throws an exception.
     */
    public function test_save_throws_exception_for_invalid_event_data()
    {
        $this->expectException(AppException::class);

        $payload = [
            // Provide missing or invalid data.
            'policyIds'            => "",
            'endDateTime'          => '2025-12-31 23:59:59',
            'nonceValidForMinutes' => 10,
        ];

        $service = new EventService();
        $service->save($payload);
    }

    /**
     * Test finding an event by ID.
     */
    public function test_find_by_id_returns_event()
    {
        $event = Event::factory()->create();

        $service = new EventService();
        $found = $service->findById($event->id);

        $this->assertNotNull($found);
        $this->assertEquals($event->id, $found->id);
    }

    /**
     * Test finding an event by UUID.
     */
    public function test_find_by_uuid_returns_event()
    {
        $event = Event::factory()->create();

        $service = new EventService();
        $found = $service->findByUUID($event->uuid);

        $this->assertNotNull($found);
        $this->assertEquals($event->id, $found->id);
    }
}
