<?php

namespace Tests\Unit;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

// The constants are already autoloaded via Composer. This check is just a safety net.
if (!defined('ROLE_ADMIN')) {
    define('ROLE_ADMIN', 'Admin');
}
if (!defined('ROLE_STAFF')) {
    define('ROLE_STAFF', 'Staff');
}

class HelpersTest extends TestCase
{
    /**
     * Ensure that after each test the Auth facade is reset.
     */
    protected function tearDown(): void
    {
        Auth::clearResolvedInstances();
        parent::tearDown();
    }

    /**
     * Test that hasRole returns false when no user is authenticated.
     */
    public function test_has_role_returns_false_when_not_authenticated()
    {
        Auth::shouldReceive('check')->once()->andReturn(false);
        $this->assertFalse(hasRole(ROLE_ADMIN));
    }

    /**
     * Test that hasRole returns false when the authenticated user does not have the required role.
     */
    public function test_has_role_returns_false_when_user_lacks_role()
    {
        $dummyUser = new \stdClass();
        $dummyUser->roles = [];

        Auth::shouldReceive('check')->once()->andReturn(true);
        Auth::shouldReceive('user')->once()->andReturn($dummyUser);

        $this->assertFalse(hasRole(ROLE_ADMIN));
    }

    /**
     * Test that hasRole returns true when the authenticated user has the required role.
     */
    public function test_has_role_returns_true_when_user_has_role()
    {
        $dummyUser = new \stdClass();
        $dummyUser->roles = [ROLE_ADMIN];

        Auth::shouldReceive('check')->once()->andReturn(true);
        Auth::shouldReceive('user')->once()->andReturn($dummyUser);

        $this->assertTrue(hasRole(ROLE_ADMIN));
    }

    /**
     * Test that isAdmin returns true when the authenticated user has the admin role.
     */
    public function test_is_admin_returns_true_for_admin_user()
    {
        $adminUser = new \stdClass();
        $adminUser->roles = [ROLE_ADMIN];

        Auth::shouldReceive('check')->once()->andReturn(true);
        Auth::shouldReceive('user')->once()->andReturn($adminUser);

        $this->assertTrue(isAdmin());
    }

    /**
     * Test that isAdmin returns false when a staff user is authenticated.
     */
    public function test_is_admin_returns_false_for_staff_user()
    {
        $staffUser = new \stdClass();
        $staffUser->roles = [ROLE_STAFF];

        Auth::shouldReceive('check')->once()->andReturn(true);
        Auth::shouldReceive('user')->once()->andReturn($staffUser);

        $this->assertFalse(isAdmin());
    }

    /**
     * Test that isStaff returns true when the authenticated user has the staff role.
     */
    public function test_is_staff_returns_true_for_staff_user()
    {
        $staffUser = new \stdClass();
        $staffUser->roles = [ROLE_STAFF];

        Auth::shouldReceive('check')->once()->andReturn(true);
        Auth::shouldReceive('user')->once()->andReturn($staffUser);

        $this->assertTrue(isStaff());
    }

    /**
     * Test that isStaff returns false when an admin user is authenticated.
     */
    public function test_is_staff_returns_false_for_admin_user()
    {
        $adminUser = new \stdClass();
        $adminUser->roles = [ROLE_ADMIN];

        Auth::shouldReceive('check')->once()->andReturn(true);
        Auth::shouldReceive('user')->once()->andReturn($adminUser);

        $this->assertFalse(isStaff());
    }

    /**
     * Test that validRoles returns the expected array of roles.
     */
    public function test_valid_roles_returns_expected_array()
    {
        $expected = [ROLE_ADMIN, ROLE_STAFF];
        $this->assertEquals($expected, validRoles());
    }

    /**
     * Test that redirectBackWithError returns a RedirectResponse with the correct flashed error message.
     */
    public function test_redirect_back_with_error()
    {
        $exception = new Exception("Test error message");
        $errorContext = "An error occurred";

        // Simulate a previous URL in session for redirect()->back() functionality.
        $this->withSession(['_previous.url' => '/previous']);

        $response = redirectBackWithError($errorContext, $exception);

        // Assert that the response is an instance of RedirectResponse.
        $this->assertInstanceOf(RedirectResponse::class, $response);

        // Send the response so the flash data is stored in session.
        $response->send();

        $expectedMessage = sprintf('%s - %s', $errorContext, $exception->getMessage());
        $this->assertEquals($expectedMessage, session('error'));
    }
}
