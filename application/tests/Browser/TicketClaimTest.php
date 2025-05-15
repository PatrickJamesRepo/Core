<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Event;
use App\Models\Ticket;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

class TicketClaimTest extends DuskTestCase
{
    /** @test */
    public function test_user_can_claim_ticket_with_wallet_asset()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();
        $ticket = Ticket::factory()->create(['eventId' => $event->id]);

        $this->browse(function (Browser $browser) use ($user, $event, $ticket) {
            // Log in as user
            $browser->loginAs($user)
                ->visit('/event/' . $event->uuid)
                ->assertSee('Claim Ticket')
                ->press('Claim Ticket')
                ->waitForText('Ticket Claimed')  // Verify ticket claimed message
                ->assertSee('Ticket Claimed');

            // Ensure the ticket status is updated in the database
            $this->assertTrue($ticket->fresh()->isCheckedIn);
        });
    }
}
