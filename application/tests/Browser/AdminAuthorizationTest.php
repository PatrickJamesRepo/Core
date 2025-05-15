<?php

namespace Tests\Browser;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    // tests/Browser/AdminAuthorizationTest.php
    public function test_non_admin_user_cannot_access_manage_events()
    {
        $user = User::factory()->create(['role' => 'user']);  // Assuming role is set for users
        $this->actingAs($user);

        $response = $this->get(route('admin.manage-events.index'));

        $response->assertStatus(403);  // Unauthorized access
    }


    /** @test */
    public function non_admin_cannot_access_manage_events_index()
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user)
            ->get(route('admin.manage-events.index'))
            ->assertStatus(403);
    }

    /** @test */
    public function admin_can_access_both_manage_users_and_manage_events()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.manage-users.index'))
            ->assertStatus(200);

        $this->actingAs($admin)
            ->get(route('admin.manage-events.index'))
            ->assertStatus(200);
    }
}
