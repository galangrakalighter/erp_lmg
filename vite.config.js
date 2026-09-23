import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        tailwindcss(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/monitoring-firebase.js',
            ],
            refresh: true,
        }),
    ],

    server: {
        host: '0.0.0.0',
        port: 5173,
        // Do not hard-code a LAN URL here.  Laravel uses the production
        // manifest when Vite is not running, while local development can be
        // reached from localhost or the current LAN host.
        cors: true,
    },
});
