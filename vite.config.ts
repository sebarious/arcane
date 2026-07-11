import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import path from 'node:path'
import inertia from '@inertiajs/vite'

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/ts/app.ts',
                'resources/css/filament/admin/theme.css',
            ],
            ssr: 'resources/ts/ssr.ts',
            refresh: true,
        }),
        inertia( {
            ssr: {
                cluster: true
            }
        } ),
        tailwindcss(),
        vue({
            template: { transformAssetUrls: { base: null, includeAbsolute: false } },
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/ts'),
        },
    },
    server: {
        host: '0.0.0.0',
        hmr: { host: 'localhost' },
    },
})
