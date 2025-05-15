<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class UIInteractionTest extends DuskTestCase
{
    public function test_navbar_dropdown_functionality()
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();
            $browser->actingAs($user)
                ->visit('/dashboard')
                ->assertSee('Dropdown')
                ->click('@dropdown-toggle')
                ->assertSee('Logout');
        });
    }

    public function test_modal_functionality()
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();
            $browser->actingAs($user)
                ->visit('/dashboard')
                ->click('@open-modal-btn')
                ->assertVisible('@modal-id')
                ->click('@close-modal-btn')
                ->assertNotVisible('@modal-id');
        });
    }
}
