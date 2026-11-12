import { defineConfig } from '@playwright/test';

export default defineConfig({
    testDir: './tests/browser',
    timeout: 180000,
    workers: 1,
    use: {
        baseURL: 'http://localhost:8080',
        viewport: { width: 1440, height: 1000 },
        launchOptions: { executablePath: process.env.COMMERCE_TEST_BROWSER || 'C:/Program Files/Google/Chrome/Application/chrome.exe' },
        screenshot: 'only-on-failure',
    },
});
