import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        hmr: {
            host: 'oxidant-class-yearbook.ngrok-free.dev', // ← ganti URL ngrok kamu
            protocol: 'wss',
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});