import { test, expect } from '@playwright/test';

test('homepage has correct title', async ({ page }) => {
    // Navigates to baseURL + '/'
    await page.goto('/');
    
    await expect(page).toHaveTitle(/GateKeeper/i);
});
