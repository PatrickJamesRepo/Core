import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
    testDir: './tests/playwright',
    timeout: 30000,
    expect: {
        timeout: 5000,
    },
    use: {
        // Use the correct base URL for your application.
        baseURL: 'https://localhost',
        // If you're using a self-signed certificate, add this:
        ignoreHTTPSErrors: true,
        headless: false, // Set to false if you want to see the browser window.
        screenshot: 'only-on-failure',
        video: 'retain-on-failure',
    },
    projects: [
        {
            name: 'chromium',
            use: {
                ...devices['Desktop Chrome'],
            },
        },
    ],
});
