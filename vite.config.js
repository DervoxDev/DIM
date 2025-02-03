import { defineConfig } from 'vite';
import laravel, { refreshPaths } from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/dervox.css',
                'resources/css/about.css',
                'resources/css/services.css',
                'resources/css/solutions.css',
                'resources/js/app.js',
                'resources/js/images.js',
            ],
            refresh: [
                ...refreshPaths,
                'app/Livewire/**',
                'app/Filament/**',
                'app/Forms/Components/**',
                'app/Infolists/Components/**',
                'app/Providers/Filament/**',
                'app/Tables/Columns/**',
                'resources/views/**',
                'resources/images/**'
            ],
        }),
    ],
    publicDir: 'public', // Add this line
});
