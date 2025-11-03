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

    // Dev
    server: {
        hmr: {
            host: 'localhost',
        },
        cors: {
            origin : /^https?:\/\/(?:(?:[^:]+\.)?localhost|10\.30\.11\.65|127\.0\.0\.1|\[::1\])(?::\d+)?$/,
        },
    },
});
