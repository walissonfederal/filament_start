import {defineConfig} from 'vite'
import laravel, {refreshPaths} from 'laravel-vite-plugin'

export default defineConfig({
    css: {
        preprocessorOptions: {
            css: {
                includePaths: [
                    'resources/css/filament/admin/theme.css',
                    'resources/css/filament/knowledge-base/theme.css'
                ],
            },
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/filament/admin/theme.css',
                'resources/css/filament/knowledge-base/theme.css',
            ],
            refresh: [
                ...refreshPaths,
                'app/Livewire/!**',
            ],
        }),
    ],
})
