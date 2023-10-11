import { defineConfig } from 'vite';
import laravel, { refreshPaths } from 'laravel-vite-plugin';
import glob from 'glob';

// Obtén la lista de archivos JS en el directorio resources/js
const jsFiles = glob.sync('resources/js/*.js');
const cssFiles = glob.sync('resources/css/*.css');

export default defineConfig({
    plugins: [
        laravel({
            input: [
                ...cssFiles, // Agrega todos los archivos css del directorio
                ...jsFiles, // Agrega todos los archivos JS del directorio
            ],
            refresh: [
                ...refreshPaths,
                'app/Livewire/**',
            ],
        }),
    ],
});
