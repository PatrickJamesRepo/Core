import { test, expect } from '@playwright/test';

test('event page displays event details', async ({ page }) => {
    // Replace with a valid event UUID or use a test event from your database.
    const eventUUID = '40a487fb-b66d-45c2-abe1-13968f87d8ef';
    await page.goto(`/event/${eventUUID}`);
    // Assert that the URL is correct.
    await expect(page).toHaveURL(new RegExp(`/event/${eventUUID}`));
    // Check that the event details (e.g., the word "Event") appear on the page.
    await expect(page.locator('text=Event')).toBeVisible();
});
