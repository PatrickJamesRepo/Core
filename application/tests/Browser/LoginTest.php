<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Support\Facades\Log;

class LoginTest extends DuskTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Clear caches first (important)
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('route:clear');

        // Run migrations + seeders on the dusk database
        Artisan::call('migrate:fresh', ['--env' => 'dusk.local']);
        Artisan::call('db:seed', ['--env' => 'dusk.local']);

        // Set the correct database connection manually (just in case)
        config(['database.default' => 'mysql_testing']);
    }


    /** @test */
    public function login_page_loads()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->screenshot('login-page-loads')  // Capture the screenshot for debugging
                ->assertSee('Login')  // Check if 'Login' text is present
                ->assertPresent('form[action="' . route('login') . '"]');  // Check if form is correctly displayed
        });
    }

    /** @test */
    public function login_form_has_all_fields()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->assertPresent('input[name="email"]')  // Check if email input is present
                ->assertPresent('input[name="password"]')  // Check if password input is present
                ->assertPresent('input[name="remember"]')  // Check if remember me checkbox is present
                ->assertPresent('input[name="_token"]')  // Ensure CSRF token is present
                ->assertPresent('button[type="submit"]')  // Ensure the submit button is available
                ->screenshot('login-form-fields');  // Capture the screenshot for debugging
        });
    }

    /** @test */
    public function user_can_login_successfully()
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => 'duskuser@example.com',
            'password' => bcrypt('secret123'), // use bcrypt(), not Hash::make()
        ]);


        // Log user creation and password check
        Log::info('DUSK TEST USER CREATED', [
            'email' => $user->email,
            'password_check' => Hash::check('secret123', $user->password),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            try {
                // Visit the login page and attempt to log in
                $browser->visit('/login')
                    ->type('email', $user->email)  // Type email
                    ->type('password', 'secret123')  // Type password
                    ->press('Login')  // Press login button
                    ->pause(2000);  // Pause for session handling

                // Log the current URL after login attempt for debugging
                $currentUrl = $browser->driver->getCurrentURL();
                Log::info('Current URL after login attempt:', ['url' => $currentUrl]);

                // Assert that after logging in, the user is redirected to /dashboard
                $browser->assertPathIs('/dashboard')  // Assert redirection
                ->assertSee('You are logged in!');  // Assert successful login message

                // Further logging after successful login
                Log::info('Login successful, redirected to dashboard.');
            } catch (\Exception $e) {
                // Log any exceptions that occur during the test
                Log::error('Login test failed', ['error' => $e->getMessage()]);

                // Take a screenshot if the test fails
                $browser->screenshot('login_failure');
                throw $e;
            }
        });
    }

    /** @test */
    public function user_can_logout_successfully()
    {
        // Create a new user for logout test
        $user = User::factory()->create([
            'email' => 'logoutuser@example.com',
            'password' => Hash::make('secret123'),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            // Log in as the created user
            $browser->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'secret123')
                ->press('Login')
                ->press('Login')
                ->waitForLocation('/dashboard', 5)
                ->assertPathIs('/dashboard')


                // Perform the logout action from the navigation dropdown
                ->click('@navbarDropdown')  // Click on the dropdown (ensure correct selector)
                ->clickLink('Logout')  // Click logout link
                ->waitForLocation('/login')  // Wait for the location to change back to /login
                ->assertPathIs('/login')  // Ensure redirected to login page after logout
                ->screenshot('logout-success');  // Screenshot after successful logout
        });
    }
}
