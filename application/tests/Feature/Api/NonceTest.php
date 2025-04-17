<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Event;
use App\Models\Ticket;
use App\Services\TicketService;
use App\ThirdParty\CardanoClients\ICardanoClient;

/**
 * NonceTest covers the entire flow of generating and validating nonces.
 *
 * In my own words:
 * - First, we check that missing fields throw proper validation errors.
 * - Then, we ensure an unknown event UUID returns a 404.
 * - Next, we simulate a successful nonce generation using a mock TicketService and Cardano client.
 * - Finally, we verify validation errors for the validate-nonce endpoint.
 */
class NonceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Clear any cached data before each test so we're always working with fresh state.
     */
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    /**
     * Test that if you call generate-nonce without any payload,
     * you get a 400 response with the expected error messages.
     */
    public function test_generate_nonce_validation_errors()
    {
        $response = $this->postJson('/api/v1/generate-nonce', []);

        $response->assertStatus(400)
            ->assertJsonStructure(['error'])
            ->assertJsonFragment([
                'error' => [
                    'The event uuid field is required.',
                    'The policy id field is required.',
                    'The asset id field is required.',
                    'The stake key field is required.',
                ],
            ]);
    }

    /**
     * Test that generating a nonce for a UUID that doesn't match any event
     * returns a 404 "Event not found" error.
     */
    public function test_generate_nonce_returns_404_when_event_not_found()
    {
        $payload = [
            'event_uuid' => Str::uuid()->toString(), // random ID
            'policy_id'  => 'policy123',
            'asset_id'   => 'asset456',
            'stake_key'  => 'stake789',
        ];

        $response = $this->postJson('/api/v1/generate-nonce', $payload);

        $response->assertStatus(404)
            ->assertJson(['error' => trans('Event not found')]);
    }

    /**
     * Test the happy path for generate-nonce.
     * In real life, this would:
     * - Verify the event exists and is active
     * - Check the asset is held in the user's wallet
     * - Create a new ticket and return a signing nonce
     *
     * Here, we mock the external Cardano client and TicketService,
     * and confirm we get back a 200 + nonce structure.
     */
    public function test_generate_nonce_success_returns_nonce()
    {
        // 1. Seed a real Event in the database with a valid timeframe
        $event = Event::factory()->create([
            'startDateTime' => Carbon::now()->subHour(),
            'endDateTime'   => Carbon::now()->addHour(),
        ]);

        // 2. Prepare test data: a real PSC policy ID + simple asset name
        $policyId  = 'f96584c4fcd13cd1702c9be683400072dd1aac853431c99037a3ab1e';
        $assetName = 'Test';
        $assetId   = $policyId . bin2hex($assetName);
        $stakeKey  = 'stake_test1qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq';

        // 3. Mock the ICardanoClient so assetHodled() always returns true
        $this->instance(ICardanoClient::class, new class implements ICardanoClient {
            public function getAssetMetadata(string $assetId): ?array { return null; }
            public function assetHodled(string $policyId, string $assetId, string $stakeKey): bool { return true; }
        });

        // 4. Create an in-memory Ticket object (no DB insert needed)
        $ticket = new Ticket([
            'eventId'        => $event->id,
            'policyId'       => $policyId,
            'assetId'        => $assetId,
            'stakeKey'       => $stakeKey,
            'signatureNonce' => random_bytes(16),
        ]);
        $ticket->created_at = Carbon::now();
        $ticket->updated_at = Carbon::now();

        // 5. Mock TicketService methods: always return our in-memory ticket
        $ticketServiceMock = $this->getMockBuilder(TicketService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findExistingTicket', 'createNewTicket'])
            ->getMock();
        $ticketServiceMock->method('findExistingTicket')->willReturn(null);
        $ticketServiceMock->method('createNewTicket')->willReturn($ticket);
        $this->app->instance(TicketService::class, $ticketServiceMock);

        // 6. Issue the API call
        $response = $this->postJson('/api/v1/generate-nonce', [
            'event_uuid' => $event->uuid,
            'policy_id'  => $policyId,
            'asset_id'   => $assetId,
            'stake_key'  => $stakeKey,
        ]);

        // 7. Finally, assert we got back a nonce in the "data" envelope
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['nonce']]);
    }

    /**
     * Test that validate-nonce with no data returns validation errors
     */
    public function test_validate_nonce_validation_errors()
    {
        $response = $this->postJson('/api/v1/validate-nonce', []);

        $response->assertStatus(400)
            ->assertJsonStructure(['error'])
            ->assertJsonFragment([
                'error' => [
                    'The event uuid field is required.',
                    'The policy id field is required.',
                    'The asset id field is required.',
                    'The stake key field is required.',
                    'The signature field is required.',
                    'The key field is required.',
                ],
            ]);
    }
}
