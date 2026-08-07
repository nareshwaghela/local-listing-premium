import { defineConfig } from 'vite';
import path from 'path';

export default defineConfig({
    build: {
        outDir: 'assets/dist',
        rollupOptions: {
            input: {
                main: 'assets/src/css/main.css',
                app: 'assets/src/js/app.js',
                'search-component': 'assets/src/js/components/search.js',
            },
            output: {
                entryFileNames: 'js/[name].js',
                chunkFileNames: 'js/[name].js',
                assetFileNames: (info) => {
                    if (info.name.endsWith('.css')) return 'css/[name].css';
                    return 'assets/[name][extname]';
                }
            }
        },
        cssCodeSplit: false,
        minify: 'terser',
    },
    resolve: {
        alias: { '@': path.resolve(__dirname, 'assets/src') }
    }
});
