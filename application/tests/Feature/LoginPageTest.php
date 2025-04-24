<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoginPageTest extends TestCase
{
    /** @test */
    public function test_login_page_renders_and_has_expected_fields()
    {
        $response = $this->get('/login');

        $response->assertStatus(200)
            ->assertSeeText('Login')               // Card title
            ->assertSeeText('Email Address')       // Email field label
            ->assertSeeText('Password')            // Password field label
            ->assertSeeText('Remember Me')         // Checkbox label
            ->assertSeeText('Forgot Your Password?'); // Link text

    }
}
