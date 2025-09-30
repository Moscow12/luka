import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js'
            ],
            refresh: true,
        }),
    ],
    css: {
        preprocessorOptions: {
            scss: {
                api: 'modern-compiler',
                silenceDeprecations: ['import', 'global-builtin', 'color-functions'],
            }
        }
    },
    resolve: {
        alias: {
            '~bootstrap': 'node_modules/bootstrap',
            '~simplebar': 'node_modules/simplebar'
        }
    }
});
