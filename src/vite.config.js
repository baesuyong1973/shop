import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.jsx',
            refresh: true,
        }),
        react(),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        hmr: {
            host: 'localhost',
        },
        watch: {
            // Docker Desktop on Windows doesn't reliably forward filesystem
            // change events into the container for bind-mounted volumes, so
            // Vite's default watcher can miss edits made from the host and
            // keep serving stale compiled output. Polling works around this.
            usePolling: true,
            interval: 300,
        },
    },
});
