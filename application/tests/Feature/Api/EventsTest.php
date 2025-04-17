<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Event;
use Illuminate\Support\Str;

class EventsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function event_list_endpoint_returns_all_events()
    {
        // Disable foreign key checks and clear events table
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Event::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        Cache::flush();

        // Seed two events
        $events = Event::factory()->count(2)->create();

        // Hit the API endpoint
        $response = $this->getJson('/api/v1/events');

        // Assert status and JSON structure
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['uuid', 'name', 'policyIds'],
                ],
            ])
            ->assertJsonCount(2, 'data');

        // Assert that returned UUIDs match seeded events
        $responseData = $response->json('data');
        $this->assertEquals(
            $events->pluck('uuid')->sort()->values()->toArray(),
            collect($responseData)->pluck('uuid')->sort()->values()->toArray()
        );
    }

    /** @test */
    public function event_info_endpoint_returns_event_data_when_exists()
    {
        // Disable foreign key checks and clear event table
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Event::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        Cache::flush();

        // Seed a single event
        $event = Event::factory()->create();

        //API endpoint for that event
        $response = $this->getJson("/api/v1/events/{$event->uuid}");

        // Assert status and JSON content (only name and policyIds are returned)
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['name', 'policyIds'],
            ])
            ->assertJson([
                'data' => [
                    'name' => $event->name,
                    'policyIds' => $event->policyIds,
                ],
            ]);
    }

    /** @test */
    public function event_info_endpoint_returns_404_when_event_not_found()
    {
        // Disable foreign key checks and clear events table
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Event::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        Cache::flush();

        // Use a random UUID that doesn't exist
        $nonexistentUuid = Str::uuid()->toString();

        $response = $this->getJson("/api/v1/events/{$nonexistentUuid}");

        // Assert 404 and error message
        $response->assertStatus(404)
            ->assertJson([
                'error' => trans('event not found'),
            ]);
    }
}
