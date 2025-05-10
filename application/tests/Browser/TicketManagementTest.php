<?php
namespace Tests\Browser;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Ticket;
use App\Models\Event;
use App\Models\User;
use Tests\TestCase;

class TicketManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function tickets_are_generated_when_event_is_created()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $event = Event::factory()->create();

        // Assuming tickets are generated automatically when event is created
        $this->assertCount(1, $event->tickets);
    }

    /** @test */
    public function test_tickets_are_listed_to_users()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();
        $ticket = Ticket::factory()->create(['eventId' => $event->id]);

        $this->actingAs($user);

        $response = $this->get(route('event.show', $event->uuid));

        $response->assertStatus(200);
        $response->assertSee($ticket->assetId);
    }

    /** @test */
    public function test_ticket_status_updates_reflected_in_ui()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $event = Event::factory()->create();
        $ticket = Ticket::factory()->create(['eventId' => $event->id]);

        $this->actingAs($user);

        $response = $this->post(route('staff.scan-tickets.ajax.register-ticket'), [
            'eventUUID' => $event->uuid,
            'qr' => $ticket->assetId . '|' . $ticket->ticketNonce,
        ]);

        $response->assertStatus(200);
        $this->assertTrue($ticket->fresh()->isCheckedIn);
    }
}
