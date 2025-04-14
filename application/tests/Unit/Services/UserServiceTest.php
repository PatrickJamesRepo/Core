<?php

namespace Tests\Unit\Services;

use App\Exceptions\AppException;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful user creation with a custom name.
     */
    public function test_create_user_successfully()
    {
        // Arrange: Data for a new user using one of your sample names.
        $data = [
            'roles'    => ['Admin'], // Assume validRoles() includes "Admin"
            'name'     => 'Chris',
            'email'    => 'chris@example.com',
            'password' => 'ValidPass123!',
        ];

        $service = new UserService();

        // Act: Create the user.
        $user = $service->create($data);

        // Assert: Verify the user was created in the database.
        $this->assertDatabaseHas('users', ['email' => 'chris@example.com']);
        $this->assertEquals('Chris', $user->name);
    }

    /**
     * Test that creation with an invalid email throws an exception.
     */
    public function test_create_user_invalid_email_throws_exception()
    {
        $this->expectException(AppException::class);

        $data = [
            'roles'    => ['User'],
            'name'     => 'Nic',  // Using Nic as a sample name
            'email'    => 'invalid-email',
            'password' => 'ValidPass123!',
        ];

        $service = new UserService();
        $service->create($data);
    }

    /**
     * Test updating an existing user.
     */
    public function test_save_updates_existing_user()
    {
        // Arrange: Create a user to later update.
        $user = User::factory()->create([
            'name'  => 'Isra',
            'email' => 'isra@example.com',
        ]);

        $payload = [
            'user_id' => $user->id,
            'roles'   => ['Admin'],
            'name'    => 'Adam',  // Update to Adam
            'email'   => 'adam@example.com',
        ];

        $service = new UserService();
        // Act: Update the user.
        $service->save($payload);

        // Assert: Check the database for the updated values.
        $this->assertDatabaseHas('users', [
            'id'    => $user->id,
            'name'  => 'Adam',
            'email' => 'adam@example.com',
        ]);
    }

    /**
     * Test changing a user's password.
     */
    public function test_change_password_works_properly()
    {
        // Arrange: Create a user with a known initial password.
        $user = User::factory()->create(['password' => bcrypt('OldPass123!')]);

        $service = new UserService();
        // Act: Change the password.
        $service->changePassword($user, 'NewPass456$');

        // Reload the user data.
        $user->refresh();

        // Assert: Verify that the password was updated.
        $this->assertTrue(password_verify('NewPass456$', $user->password));
    }

    /**
     * Test that validateCurrentPassword throws an exception if the password is incorrect.
     */
    public function test_validate_current_password_fails_for_wrong_password()
    {
        $user = User::factory()->create(['password' => bcrypt('CorrectPass')]);
        $service = new UserService();

        $this->expectException(AppException::class);
        $service->validateCurrentPassword($user->id, 'WrongPass');
    }

    /**
     * Test updating a user's account.
     */
    public function test_update_account_updates_name_and_password()
    {
        // Arrange: Create a user with initial values.
        $user = User::factory()->create([
            'name'     => 'Isra',
            'password' => bcrypt('OriginalPass'),
        ]);
        $service = new UserService();

        // Act: Update the account with a new name and password.
        $service->updateAccount($user->id, 'Chris', 'UpdatedPass!');

        // Refresh the user instance.
        $user->refresh();

        // Assert: Confirm that the name and password have been updated.
        $this->assertEquals('Chris', $user->name);
        $this->assertTrue(password_verify('UpdatedPass!', $user->password));
    }
}
