import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/form-enhancements.css',
                'resources/js/app.js',
                'resources/js/admin.js',
                'resources/js/admin-dashboard.js',
                'resources/js/admin-financial.js',
                'resources/js/admin-analytics-visits.js',
            ],
            refresh: true,
        }),
    ],
});