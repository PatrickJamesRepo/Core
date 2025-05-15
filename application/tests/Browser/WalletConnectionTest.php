<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use App\Models\User;
use App\Models\Event;

class WalletConnectionTest extends DuskTestCase
{
    /** @test */
    public function test_user_can_connect_their_cardano_wallet()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();

        $this->browse(function (Browser $browser) use ($user, $event) {
            // Log in as user
            $browser->loginAs($user)
                ->visit('/event/' . $event->uuid)
                ->assertSee('Connect Wallet')  // Ensure "Connect Wallet" button is visible
                ->press('Connect Wallet')
                ->waitForText('Wallet Connected')  // Verify successful connection message
                ->assertSee('Wallet Connected')
                ->assertSee('Connected to Cardano'); // Add confirmation that wallet is connected to Cardano network
        });
    }
}
