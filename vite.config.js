import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.ts',
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],

    build: {
        // Production build optimization
        minify: 'terser',
        sourcemap: process.env.NODE_ENV === 'production' ? false : true,
        cssCodeSplit: true,
        rollupOptions: {
            output: {
                // Code splitting for better caching
                manualChunks: {
                    'vue': ['vue'],
                    'inertia': ['@inertiajs/vue3'],
                    'ui': ['@headlessui/vue', '@heroicons/vue'],
                    'charts': ['chart.js', 'vue-chartjs'],
                },
                // Asset naming
                assetFileNames: (assetInfo) => {
                    const info = assetInfo.name.split('.');
                    const ext = info[info.length - 1];
                    if (/png|jpe?g|gif|svg/.test(ext)) {
                        return `images/[name]-[hash][extname]`;
                    } else if (/woff|woff2|eot|ttf|otf/.test(ext)) {
                        return `fonts/[name]-[hash][extname]`;
                    } else if (ext === 'css') {
                        return `css/[name]-[hash][extname]`;
                    }
                    return `[name]-[hash][extname]`;
                },
            },
        },
    },

    // Production environment optimizations
    define: {
        __DEV__: JSON.stringify(process.env.NODE_ENV !== 'production'),
    },

    // Optimize dependencies
    optimizeDeps: {
        include: ['vue', '@inertiajs/vue3', 'axios'],
    },
});
