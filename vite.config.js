import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        tailwindcss(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        {
            name: 'local-network-access',
            configureServer(server) {
                server.middlewares.use((req, res, next) => {
                    if (req.method === 'OPTIONS') {
                        res.setHeader('Access-Control-Allow-Private-Network', 'true');
                    }
                    next();
                });
            },
        },
    ],
    server: {
        host: 'localhost',
        port: 5173,
        cors: {
            origin: true,
            methods: ['GET', 'HEAD', 'OPTIONS'],
            allowedHeaders: ['Content-Type'],
        },
        allowedHosts: true,
    },
});