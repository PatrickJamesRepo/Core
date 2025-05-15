<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Event;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class UnauthorizedAccessTest extends DuskTestCase
{
    public function test_non_admin_user_cannot_access_event_management()
    {
        $user = User::factory()->create(); // Non-admin user
        $event = Event::factory()->create();

        $this->browse(function (Browser $browser) use ($user, $event) {
            $browser->loginAs($user)
                ->visit("/admin/manage-events/{$event->id}/edit")
                ->assertSee('Unauthorized')  // Expect to see an unauthorized message
                ->assertPathIs('/home');  // Redirect to home or login
        });
    }
}
