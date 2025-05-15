<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ManageUsersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_sees_user_list_and_existing_users()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $alice = User::factory()->create(['name' => 'Alice']);
        $bob   = User::factory()->create(['name' => 'Bob']);

        $response = $this->actingAs($admin)
            ->get(route('admin.manage-users.index'));

        $response->assertStatus(200)
            ->assertSee('Alice')
            ->assertSee('Bob');
    }

    /** @test */
    public function admin_can_create_a_new_user_via_save_route()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $payload = [
            'name'     => 'New User',
            'email'    => 'new@example.com',
            'password' => 'secret123',
            'roles'    => ['user', 'staff'],
        ];

        $this->actingAs($admin)
            ->post(route('admin.manage-users.save'), $payload)
            ->assertStatus(302)
            ->assertSessionHas('status');

        $this->assertDatabaseHas('users', [
            'email' => 'new@example.com',
            'name'  => 'New User',
        ]);
    }

    /** @test */
    public function admin_can_edit_existing_user()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $joe = User::factory()->create(['name' => 'Joe']);

        $update = [
            'user_id'  => $joe->id,
            'name'     => 'Joseph',
            'email'    => $joe->email,
            'roles'    => ['admin'],
        ];

        $this->actingAs($admin)
            ->post(route('admin.manage-users.save'), $update)
            ->assertStatus(302)
            ->assertSessionHas('status');

        $this->assertDatabaseHas('users', [
            'id'   => $joe->id,
            'name' => 'Joseph',
        ]);
    }
}
