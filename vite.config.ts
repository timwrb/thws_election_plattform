import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/filament/admin/theme.css',
                'resources/css/filament/electives/theme.css',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
