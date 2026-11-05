import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig({
    resolve: { alias: { 'web-shared': fileURLToPath(new URL('./resources/js/commerce/shared/index.ts', import.meta.url)) } },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/commerce/shopping.tsx', 'resources/js/commerce/merchant.tsx'],
            refresh: true,
        }),
        tailwindcss(),
        react(),
    ],
});
