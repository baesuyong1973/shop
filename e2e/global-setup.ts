import { execSync } from 'node:child_process';
import http from 'node:http';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const E2E_DATABASE = 'laravel_e2e';
const BASE_URL = 'http://localhost:8090';
const repoRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');

function run(command: string): string {
    return execSync(command, { cwd: repoRoot, encoding: 'utf8', stdio: ['ignore', 'pipe', 'inherit'] });
}

// Uses node:http rather than fetch: Node's fetch (undici) can crash the
// process on how PHP's built-in server closes connections.
function statusOf(url: string): Promise<number> {
    return new Promise((resolve) => {
        http.get(url, (response) => {
            response.resume();
            resolve(response.statusCode ?? 0);
        }).on('error', () => resolve(0));
    });
}

async function waitForServer(url: string, timeoutMs = 60_000): Promise<void> {
    const deadline = Date.now() + timeoutMs;
    while (Date.now() < deadline) {
        const status = await statusOf(url);
        if (status > 0 && status < 500) {
            return;
        }
        await new Promise((resolve) => setTimeout(resolve, 1000));
    }
    throw new Error(`E2E server did not become ready at ${url}`);
}

/**
 * Start the dedicated E2E app server and reset its database to the seeded
 * state, so every run starts from the same known data.
 */
export default async function globalSetup(): Promise<void> {
    run('docker compose --profile e2e up -d e2e');

    // The E2E server serves built assets (see VITE_HOT_FILE in
    // docker-compose.yml), which is much faster than the Vite dev server;
    // rebuild them so the run tests the current frontend code.
    run('docker compose exec -T vite npm run build');

    run(
        `docker compose exec -T db mysql -uroot -proot -e ` +
            `"CREATE DATABASE IF NOT EXISTS ${E2E_DATABASE}; ` +
            `GRANT ALL PRIVILEGES ON ${E2E_DATABASE}.* TO 'laravel'@'%';"`,
    );

    // migrate:fresh drops every table, so refuse to run unless the E2E
    // container really points at the E2E database.
    const database = JSON.parse(run('docker compose exec -T e2e php artisan db:show --json')).platform.config.database;

    if (database !== E2E_DATABASE) {
        throw new Error(`Refusing to reset database "${database}"; expected "${E2E_DATABASE}".`);
    }

    // The E2E server shares storage/ with dev, so remove images uploaded by
    // the previous run (referenced by the E2E database but not by dev's)
    // before the reset forgets them.
    let leftoverOutput = '';
    try {
        leftoverOutput = run(
            'docker compose exec -T db mysql -ularavel -psecret -N -e ' +
                `"SELECT image_path FROM ${E2E_DATABASE}.products ` +
                'WHERE image_path NOT IN (SELECT image_path FROM laravel.products)"',
        );
    } catch {
        // First run: the E2E database has no tables yet.
    }
    const leftoverImages = leftoverOutput
        .split(/\r?\n/)
        .map((line) => line.trim())
        .filter((line) => /^products\/[A-Za-z0-9]+\.jpg$/.test(line));

    if (leftoverImages.length > 0) {
        run(`docker compose exec -T e2e rm -f ${leftoverImages.map((p) => `storage/app/public/${p}`).join(' ')}`);
    }

    run('docker compose exec -T e2e php artisan migrate:fresh --seed --force');

    await waitForServer(BASE_URL);
}
