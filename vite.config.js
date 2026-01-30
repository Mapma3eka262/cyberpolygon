import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/auth.css',
                'resources/js/auth.js',
                'resources/css/dashboard.css',
                'resources/js/dashboard.js',
                'resources/css/arena.css',
                'resources/js/arena.js',
                'resources/css/admin.css',
                'resources/js/admin.js'
            ],
            refresh: true,
        }),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    'vendor': ['bootstrap', '@popperjs/core'],
                    'charts': ['chart.js'],
                    'htmx': ['htmx.org']
                }
            }
        },
        chunkSizeWarningLimit: 1000
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        hmr: {
            host: 'localhost'
        }
    }
});