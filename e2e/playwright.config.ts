import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
    testDir: './tests',
    globalSetup: './global-setup.ts',
    // Every spec runs against the same freshly seeded database, and some
    // specs change stock levels, so run them one at a time in file order.
    fullyParallel: false,
    workers: 1,
    retries: 0,
    // Generous: the app runs off a Windows bind mount, which is slow.
    timeout: 120_000,
    expect: { timeout: 20_000 },
    reporter: [['list'], ['html', { open: 'never' }]],
    use: {
        baseURL: 'http://localhost:8090',
        locale: 'ja-JP',
        trace: 'retain-on-failure',
        screenshot: 'only-on-failure',
        video: 'retain-on-failure',
    },
    projects: [
        // Runs after globalSetup has reset the database (sessions included).
        {
            name: 'setup',
            testMatch: /.*\.setup\.ts/,
            use: { ...devices['Desktop Chrome'] },
        },
        {
            name: 'chromium',
            dependencies: ['setup'],
            use: { ...devices['Desktop Chrome'] },
        },
    ],
});
