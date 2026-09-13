import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/style/app.scss', 'resources/js/app.js', 'resources/js/editorial.js', 'resources/js/reviewer.js'],
            refresh: true,
        }),
        vue()
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    }
});
