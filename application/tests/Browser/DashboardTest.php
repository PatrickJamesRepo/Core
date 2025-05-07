<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class DashboardTest extends DuskTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate:fresh', ['--env' => 'dusk.testing']);
        Artisan::call('db:seed',    ['--env' => 'dusk.testing']);
    }

    public function test_admin_sees_all_dashboard_components()
    {
        $user = User::factory()->create([
            'roles'    => ['admin', 'staff'],
            'password' => bcrypt('secret123'),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/dashboard')
                ->waitFor('.card-header', 5)
                ->assertSeeIn('.card-header', 'Dashboard')
                ->assertSeeIn('.card-body', 'You are logged in!')
                ->assertSeeLink('Manage Users')
                ->assertSeeLink('Manage Event')
                ->assertSeeLink('Scan Tickets');
        });
    }

    public function test_staff_sees_only_staff_components()
    {
        $user = User::factory()->create([
            'roles'    => ['staff'],
            'password' => bcrypt('secret123'),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/dashboard')
                ->waitFor('.card-header', 5)
                ->assertDontSee('Manage Users')
                ->assertDontSee('Manage Event')
                ->assertSeeLink('Scan Tickets');
        });
    }
}
