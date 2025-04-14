import { test, expect } from '@playwright/test';

test.describe.serial('Total Front-End Flow', () => {

    test('Regular user can log in and access dashboard', async ({ page }) => {
        // Navigate to the login page.
        await page.goto('/login');

        // Fill in login form fields for a regular user.
        await page.fill('input[name="email"]', 'patrick@patrick.com');
        await page.fill('input[name="password"]', 'password');

        // Click the login button.
        await page.click('button[type="submit"]');

        // Wait for a known dashboard element that appears after login.
        await expect(page.locator('text=Dashboard')).toBeVisible({ timeout: 30000 });

        // Assert that the URL has updated to something like /dashboard.
        await expect(page).toHaveURL(/dashboard/);

        // Optionally, perform additional actions on the dashboard here.

        // Log out regular user.
        await page.click('button#logout');
        await expect(page).toHaveURL(/login/);
    });

    test('Admin can log in and access admin dashboard', async ({ page }) => {
        // Navigate to the login page.
        await page.goto('/login');

        // Fill in login form fields using admin credentials.
        await page.fill('input[name="email"]', 'admin@example.com');
        await page.fill('input[name="password"]', 'adminpassword');

        // Click the login button and optionally wait for the login API.
        const [loginResponse] = await Promise.all([
            page.waitForResponse(response =>
                response.url().includes('/api/login') && response.status() === 200
            ),
            page.click('button[type="submit"]')
        ]);
        expect(loginResponse.status()).toBe(200);

        // Wait for a known admin dashboard element to be visible.
        await expect(page.locator('text=Admin Dashboard')).toBeVisible({ timeout: 30000 });

        // Assert that the URL indicates a successful login.
        await expect(page).toHaveURL(/dashboard/);

        // Optionally, navigate to other admin-specific pages or perform further checks.

        // Log out admin.
        await page.click('button#logout');
        await expect(page).toHaveURL(/login/);
    });

});
