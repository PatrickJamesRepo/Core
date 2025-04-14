<?php

namespace Tests\Unit\Services;

use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class TicketServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a new ticket is created correctly.
     */
    public function test_create_new_ticket_creates_record()
    {
        $service = new TicketService();
        $ticket = $service->createNewTicket(1, 'Policy1', 'Asset1', 'StakeKey1');

        // Assert ticket record exists in the database.
        $this->assertDatabaseHas('tickets', [
            'eventId'  => 1,
            'policyId' => 'Policy1',
            'assetId'  => 'Asset1',
            'stakeKey' => 'StakeKey1',
        ]);

        $this->assertNotNull($ticket->signatureNonce);
    }

    /**
     * Test finding an existing ticket.
     */
    public function test_find_existing_ticket_returns_correct_ticket()
    {
        // Arrange: Create a ticket record.
        $ticket = Ticket::factory()->create([
            'eventId'  => 1,
            'policyId' => 'PolicyA',
            'assetId'  => 'AssetA',
            'stakeKey' => 'StakeA',
        ]);

        $service = new TicketService();
        // Act: Retrieve the ticket.
        $foundTicket = $service->findExistingTicket(1, 'PolicyA', 'AssetA', 'StakeA');

        // Assert: Verify the ticket was found.
        $this->assertNotNull($foundTicket);
        $this->assertEquals($ticket->id, $foundTicket->id);
    }

    /**
     * Test updating ticket nonce and signature.
     */
    public function test_set_ticket_nonce_and_signature_updates_ticket()
    {
        // Arrange: Create a ticket record.
        $ticket = Ticket::factory()->create();
        $service = new TicketService();
        $signature = 'TestSignature';

        // Act: Update the ticket.
        $service->setTicketNonceAndSignature($ticket, $signature);
        $ticket->refresh();

        // Assert: Confirm signature and ticketNonce are updated.
        $this->assertEquals($signature, $ticket->signature);
        $this->assertNotNull($ticket->ticketNonce);
    }
}
