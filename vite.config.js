import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': resolve(__dirname, './resources/js'),
    },
  },
  build: {
    outDir: 'dist',
    emptyOutDir: true,
    lib: {
      entry: resolve(__dirname, 'resources/js/index.js'),
      name: 'InertiaDatatable',
      fileName: (format) => `inertia-datatable.${format}.js`,
    },
    rollupOptions: {
      external: ['vue', '@inertiajs/vue3'],
      output: {
        globals: {
          vue: 'Vue',
          '@inertiajs/vue3': 'Inertia',
        },
      },
    },
  },
});
