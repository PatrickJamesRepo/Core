<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Models\Event;
use App\Models\Ticket;
use App\Services\TicketService;

class StaffScanTicketsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Simulate an authenticated user so Auth::id() is non-null
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);
        // Bypass staff.only middleware for controller behavior tests
        $this->withoutMiddleware();
        Cache::flush();
    }

    /** @test */
    public function index_redirects_to_first_event_scan()
    {
        // Arrange: seed two events
        $events = Event::factory()->count(2)->create();

        // Act: hit the index
        $response = $this->get('/staff/scan-tickets');

        // Assert: redirect to scan page for first event
        $response->assertRedirect(
            route('staff.scan-tickets.event', $events->first()->uuid)
        );
    }

    /** @test */
    public function event_page_displays_scan_view_for_valid_event()
    {
        // Arrange: seed one event
        $event = Event::factory()->create();

        // Act: view scan page for that event
        $response = $this->get("/staff/scan-tickets/{$event->uuid}");

        // Assert: 200 OK, correct view and data
        $response->assertStatus(200)
            ->assertViewIs('staff.scan-tickets.event')
            ->assertViewHas('event', $event);
    }

    /** @test */
    public function event_page_returns_404_for_invalid_uuid()
    {
        // Act: hit non-existent event UUID
        $response = $this->get('/staff/scan-tickets/' . Str::uuid());

        // Assert 404 Not Found
        $response->assertStatus(404);
    }

    /** @test */
    public function ajax_register_ticket_errors_on_missing_params()
    {
        // Act: empty payload
        $response = $this->postJson('/staff/scan-tickets/ajax/register-ticket', []);

        // Assert: 500 with "Invalid request"
        $response->assertStatus(500)
            ->assertJson(['error' => trans('Invalid request')]);
    }

    /** @test */
    public function ajax_register_ticket_errors_when_event_not_found()
    {
        // Arrange: random UUID, valid qr format
        $payload = [
            'eventUUID' => Str::uuid()->toString(),
            'qr'        => 'asset123|nonce456',
        ];

        // Act
        $response = $this->postJson('/staff/scan-tickets/ajax/register-ticket', $payload);

        // Assert: 500 with "Event not found"
        $response->assertStatus(500)
            ->assertJson(['error' => trans('Event not found')]);
    }

    /** @test */
    public function ajax_register_ticket_errors_when_ticket_not_found()
    {
        // Arrange: seed event
        $event = Event::factory()->create();

        // Mock TicketService to return null
        $ticketServiceMock = $this->getMockBuilder(TicketService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findTicketByQRCode', 'checkInTicket'])
            ->getMock();
        $ticketServiceMock->method('findTicketByQRCode')->willReturn(null);
        $this->app->instance(TicketService::class, $ticketServiceMock);

        $payload = [
            'eventUUID' => $event->uuid,
            'qr'        => 'asset123|nonce456',
        ];

        // Act
        $response = $this->postJson('/staff/scan-tickets/ajax/register-ticket', $payload);

        // Assert: 500 with "Invalid ticket"
        $response->assertStatus(500)
            ->assertJson(['error' => trans('Invalid ticket')]);
    }

    /** @test */
    public function ajax_register_ticket_successful_registration_returns_success_message()
    {
        // Arrange: seed event
        $event = Event::factory()->create();
        $assetId = 'asset123';
        $nonce   = 'nonce456';

        // Prepare a Ticket model instance (in-memory)
        $ticket = new Ticket([
            'eventId'        => $event->id,
            'policyId'       => 'policy',
            'assetId'        => $assetId,
            'stakeKey'       => 'stake',
            'signatureNonce' => random_bytes(16),
        ]);
        $ticket->isCheckedIn = false;
        $ticket->checkInTime = now();

        // Mock TicketService to return our ticket and accept checkIn
        $ticketServiceMock = $this->getMockBuilder(TicketService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findTicketByQRCode', 'checkInTicket'])
            ->getMock();
        $ticketServiceMock->method('findTicketByQRCode')->willReturn($ticket);
        $ticketServiceMock->method('checkInTicket')->willReturnCallback(function() {});
        $this->app->instance(TicketService::class, $ticketServiceMock);

        // Act: issue AJAX request
        $response = $this->postJson('/staff/scan-tickets/ajax/register-ticket', [
            'eventUUID' => $event->uuid,
            'qr'        => "{$assetId}|{$nonce}",
        ]);

        // Assert: 200 with success message
        $response->assertStatus(200)
            ->assertJson(['data' => ['success' => trans('Ticket successfully registered')]]);
    }
}
