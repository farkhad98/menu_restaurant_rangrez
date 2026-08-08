import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
    plugins: [vue()],
    publicDir: false,

    build: {
        outDir: 'public/build',
        emptyOutDir: true,
        rollupOptions: {
            input: 'resources/js/app.js',
            output: {
                entryFileNames: 'app.js',
                chunkFileNames: 'chunks/[name]-[hash].js',
                assetFileNames: assetInfo => {
                    if (assetInfo.name && assetInfo.name.endsWith('.css')) {
                        return 'app.css'
                    }

                    return 'assets/[name]-[hash][extname]'
                }
            }
        }
    }
})
