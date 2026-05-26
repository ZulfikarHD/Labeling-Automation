import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    build: {
        assetsDir: 'assets',
        base: '/labeling/'
    },

    // Dev Enable this, Prod Comment This
    server: {
        host: '0.0.0.0', // Listen on all network interfaces
        port: 5173,
        hmr: {
            host: '10.30.11.65', // Use the server's IP for HMR
            port: 5173,
        },
        cors: {
            origin: true, // Allow all origins during development
        },
    },
});
