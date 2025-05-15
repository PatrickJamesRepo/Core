<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Event;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;

class WalletErrorHandlingTest extends DuskTestCase
{
    /** @test */
    public function test_user_is_prompted_when_wallet_is_not_connected()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();

        $this->browse(function (Browser $browser) use ($user, $event) {
            // Log in as user without wallet connected
            $browser->loginAs($user)
                ->visit('/event/' . $event->uuid)
                ->assertSee('Connect Wallet')  // Ensure wallet connection is required
                ->press('Claim Ticket')
                ->waitForText('Please connect your wallet')  // Verify wallet connection prompt
                ->assertSee('Please connect your wallet')
                ->assertSee('You need to connect a Cardano wallet to claim tickets'); // Custom message for clarity
        });
    }
}
