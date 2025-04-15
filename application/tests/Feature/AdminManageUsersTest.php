<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminManageUsersTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper function to create an admin user.
     *
     * We assign the 'admin' role to the user to ensure that
     * the AdminOnly middleware allows access to admin routes.
     * Roles are stored as a JSON‑encoded array via the custom cast.
     */
    protected function adminUser(): User
    {
        $admin = User::factory()->create([
            'name'     => 'Admin User',
            'email'    => 'admin@example.com',
            'password' => 'secret123', // Plain text; the custom cast will hash it.
        ]);
        // Assign roles as a plain PHP array so that the custom cast correctly encodes it.
        $admin->roles = ['admin'];
        $admin->save();

        return $admin;
    }

    /**
     * Test that an admin can access the users index page.
     */
    public function test_admin_can_access_users_index()
    {
        $admin = $this->adminUser();
        $response = $this->actingAs($admin)->get(route('admin.manage-users.index'));
        $response->assertStatus(200);
        $response->assertSee('Manage Users');
    }

    /**
     * Test that an admin can access the Add User form.
     *
     * Note: The route name in the routes file for the Add User form is defined
     * as 'admin.manage-users.add-user'. Ensure this matches your routes configuration.
     */
    public function test_admin_can_access_add_user_form()
    {
        $admin = $this->adminUser();
        $response = $this->actingAs($admin)->get(route('admin.manage-users.add-user'));
        $response->assertStatus(200);
        // Adjust the text to match what is expected in your add user form.
        $response->assertSee('Account Name');
    }

    /**
     * Test that an admin can edit an existing user.
     */
    public function test_admin_can_edit_existing_user()
    {
        $admin = $this->adminUser();

        // Create a regular user to be edited.
        $user = User::factory()->create([
            'name'     => 'Test User',
            'email'    => 'user@example.com',
            'password' => 'secret123',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.manage-users.edit', $user->id));
        $response->assertStatus(200);
        $response->assertSee($user->name);
    }

    /**
     * Test that editing a non-existing user redirects with an error.
     */
    public function test_editing_nonexistent_user_redirects()
    {
        $admin = $this->adminUser();
        $nonExistentUserId = 999;

        $response = $this->actingAs($admin)->get(route('admin.manage-users.edit', $nonExistentUserId));
        $response->assertRedirect(route('admin.manage-users.index'));
        $response->assertSessionHas('error', trans('user does not exist'));
    }

    /**
     * Test that an admin can save (create) a new user.
     */
    public function test_admin_can_save_new_user()
    {
        $admin = $this->adminUser();
        $userData = [
            'name'     => 'New User',
            'email'    => 'newuser@example.com',
            'password' => 'secret123', // Plain text; custom cast handles hashing.
            'roles'    => ['user'],    // Assign regular user role.
        ];

        $response = $this->actingAs($admin)->post(route('admin.manage-users.save'), $userData);
        // Assert the redirect using the route helper function.
        $response->assertRedirect(route('admin.manage-users.index'));
        $response->assertSessionHas('status', trans('account created'));

        // Verify that the user exists in the database.
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'name'  => 'New User',
        ]);
    }

    /**
     * Test that an admin can update an existing user.
     */
    public function test_admin_can_update_existing_user()
    {
        $admin = $this->adminUser();

        // Create a user to update.
        $user = User::factory()->create([
            'name'     => 'Old Name',
            'email'    => 'updateuser@example.com',
            'password' => 'secret123',
        ]);

        $updatedData = [
            'user_id'  => $user->id,
            'name'     => 'Updated Name',
            'email'    => 'updateuser@example.com', // Email remains unchanged.
            'password' => 'secret123',              // Plain text.
            'roles'    => ['user'],
        ];

        $response = $this->actingAs($admin)->post(route('admin.manage-users.save'), $updatedData);
        $response->assertRedirect(route('admin.manage-users.index'));
        $response->assertSessionHas('status', trans('account updated'));

        // Verify that the user's name has been updated in the database.
        $this->assertDatabaseHas('users', [
            'id'   => $user->id,
            'name' => 'Updated Name',
        ]);
    }
}
