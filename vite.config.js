import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

// Çdo build prodhon skedarë me emra të ndryshëm (timestamp) që cache-i të mos shërbejë asete të vjetra pas deploy.
const buildId = Date.now();

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
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
        // heic2any ~1.35 MB — pritet; nuk është bug
        chunkSizeWarningLimit: 1500,
        rollupOptions: {
            output: {
                entryFileNames: `assets/[name]-[hash]-${buildId}.js`,
                chunkFileNames: `assets/[name]-[hash]-${buildId}.js`,
                assetFileNames: `assets/[name]-[hash]-${buildId}.[ext]`,
                manualChunks(id) {
                    if (id.includes('node_modules/vue/') || id.includes('node_modules/vue-router/')) {
                        return 'vue-vendor';
                    }
                },
            },
        },
    },
});
