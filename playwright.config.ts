import { defineConfig, devices } from '@playwright/test';

const port = process.env.E2E_PORT || '8765';
const baseURL = process.env.E2E_BASE_URL || 'http://127.0.0.1:' + port;

export default defineConfig({
    testDir: './tests/e2e',
    use: { baseURL, trace: 'retain-on-failure', ...devices['Desktop Chrome'], channel: process.env.E2E_BROWSER_CHANNEL },
    webServer: process.env.E2E_BASE_URL ? undefined : {
        command: 'bash scripts/e2e-server.sh',
        env: { E2E_PORT: port },
        url: baseURL + '/up',
        timeout: 60_000,
    },
});
