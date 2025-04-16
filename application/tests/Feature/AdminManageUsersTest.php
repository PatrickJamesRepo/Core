<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminManageUsersTest extends TestCase
{
    use RefreshDatabase;

    protected function adminUser(): User
    {
        $admin = User::factory()->create([
            'name'     => 'Admin User',
            'email'    => 'admin@example.com',
            'password' => 'StrongP@ssw0rd!', // Strong, meets validation rules
        ]);
        $admin->roles = ['admin'];
        $admin->save();

        return $admin;
    }

    public function test_admin_can_access_users_index()
    {
        $admin = $this->adminUser();
        $response = $this->actingAs($admin)->get(route('admin.manage-users.index'));
        $response->assertStatus(200);
        $response->assertSee('Manage Users');
    }

    public function test_admin_can_access_add_user_form()
    {
        $admin = $this->adminUser();
        $response = $this->actingAs($admin)->get(route('admin.manage-users.add-user'));
        $response->assertStatus(200);
        $response->assertSee('Account Name');
    }

    public function test_admin_can_edit_existing_user()
    {
        $admin = $this->adminUser();
        $user = User::factory()->create([
            'name'     => 'Test User',
            'email'    => 'user@example.com',
            'password' => 'StrongP@ssw0rd!',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.manage-users.edit', $user->id));
        $response->assertStatus(200);
        $response->assertSee($user->name);
    }

    public function test_editing_nonexistent_user_redirects()
    {
        $admin = $this->adminUser();
        $nonExistentUserId = 999;

        $response = $this->actingAs($admin)->get(route('admin.manage-users.edit', $nonExistentUserId));
        $response->assertRedirect(route('admin.manage-users.index'));
        $response->assertSessionHas('error', trans('user does not exist'));
    }

    public function test_admin_can_save_new_user()
    {
        $admin = $this->adminUser();
        $userData = [
            'name'     => 'New User',
            'email'    => 'newuser@example.com',
            'password' => 'Str0ng!NewP@ssword', // Valid strong password
            'roles'    => ['user'],
        ];

        $response = $this->followingRedirects()->actingAs($admin)->post(route('admin.manage-users.save'), $userData);
        $response->assertSee(trans('account created'));

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'name'  => 'New User',
        ]);
    }

    public function test_admin_can_update_existing_user()
    {
        $admin = $this->adminUser();

        $user = User::factory()->create([
            'name'     => 'Old Name',
            'email'    => 'updateuser@example.com',
            'password' => 'StrongP@ssw0rd!',
        ]);

        $updatedData = [
            'user_id'  => $user->id,
            'name'     => 'Updated Name',
            'email'    => 'updateuser@example.com',
            'password' => 'An0ther!ValidPass',
            'roles'    => ['user'],
        ];

        $response = $this->followingRedirects()->actingAs($admin)->post(route('admin.manage-users.save'), $updatedData);
        $response->assertSee(trans('account updated'));

        $this->assertDatabaseHas('users', [
            'id'   => $user->id,
            'name' => 'Updated Name',
        ]);
    }
}
