import { test, expect } from '@playwright/test';

test.describe('Authentication Tests', () => {

    test('should log in successfully', async ({ page, context }) => {
        // Navigate to the login page.
        await page.goto('/login');

        // Fill in the login form.
        await page.fill('input[name="email"]', 'admin@example.com');
        await page.fill('input[name="password"]', 'adminpassword');

        // Intercept the login API request and click the login button.
        const [response] = await Promise.all([
            page.waitForResponse(resp => resp.url().includes('/api/login') && resp.status() === 200),
            page.click('button[type="submit"]'),
        ]);
        expect(response.status()).toBe(200);

        // Verify that the user is redirected to the dashboard.
        await expect(page).toHaveURL(/dashboard/);
        await expect(page.locator('text=Dashboard')).toBeVisible();

        // Check that session cookies (e.g., session_id or auth_token) are set.
        const cookies = await context.cookies();
        const sessionCookie = cookies.find(cookie => cookie.name === 'session_id' || cookie.name === 'auth_token');
        expect(sessionCookie).toBeDefined();
    });

    test('should display error with invalid credentials', async ({ page }) => {
        // Navigate to the login page.
        await page.goto('/login');

        // Fill in the login form with invalid credentials.
        await page.fill('input[name="email"]', 'admin@example.com');
        await page.fill('input[name="password"]', 'wrongpassword');

        // Click the login button.
        await page.click('button[type="submit"]');

        // Assert that an error message is visible.
        await expect(page.locator('.error-message')).toBeVisible();

        // Assert that the URL remains on the login page.
        await expect(page).toHaveURL(/login/);
    });

    test('should log out successfully', async ({ page, context }) => {
        // First log in successfully.
        await page.goto('/login');
        await page.fill('input[name="email"]', 'admin@example.com');
        await page.fill('input[name="password"]', 'adminpassword');
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/dashboard/);

        // Click the logout button.
        await page.click('button#logout');

        // Verify that the user is redirected back to the login page.
        await expect(page).toHaveURL(/login/);

        // Optionally, check that the session cookie is removed.
        const cookies = await context.cookies();
        const sessionCookie = cookies.find(cookie => cookie.name === 'session_id' || cookie.name === 'auth_token');
        expect(sessionCookie).toBeUndefined();
    });

});
