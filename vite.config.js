import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
            laravel({
                input: 'resources/js/app.ts',
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
        // Ensure Laravel can find Vite's manifest at public/build/manifest.json
        outDir: 'public/build',
        emptyOutDir: true,
        manifest: 'manifest.json',

        // Production build optimization
        minify: 'terser',
        sourcemap: process.env.NODE_ENV === 'production' ? false : true,
        cssCodeSplit: true,
        rollupOptions: {
            output: {
                // Code splitting for better caching
                    manualChunks: (id) => {
                        if (id.includes('node_modules')) {
                            if (
                                id.includes('@inertiajs/vue3') ||
                                id.includes('vue')
                            ) {
                                return 'vendor';
                            }
                            if (id.includes('chart.js') || id.includes('vue-chartjs')) {
                                return 'charts';
                            }
                            if (id.includes('@headlessui') || id.includes('@heroicons')) {
                                return 'ui';
                            }
                            return 'vendor';
                        }
                    },
                // Asset naming
                    assetFileNames: (assetInfo) => {
                        const info = assetInfo.name.split('.');
                        const ext = info[info.length - 1];
                        if (/png|jpe?g|gif|svg/.test(ext)) {
                            return 'images/[name]-[hash][extname]';
                        } else if (/woff|woff2|eot|ttf|otf/.test(ext)) {
                            return 'fonts/[name]-[hash][extname]';
                        } else if (ext === 'css') {
                            return 'css/[name]-[hash][extname]';
                        }
                        return '[name]-[hash][extname]';
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
