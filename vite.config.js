import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/images/login/nubes-superiores.svg',
                'resources/images/login/nubes-inferiores.svg',
                'resources/images/login/moneda.svg',
                'resources/images/login/icono-usuario.svg',
                'resources/images/login/icono-contrasena.svg',
                'resources/images/login/icono-ingreso.svg',
                'resources/images/login/logo-katoki.png',
                'resources/images/portal/icono-bienvenida.svg',
                'resources/images/portal/icono-retroceso.svg',
                'resources/images/portal/niveles/nivel-1.svg',
                'resources/images/portal/niveles/nivel-2.svg',
                'resources/images/portal/niveles/nivel-2-bloqueado.svg',
                'resources/images/portal/niveles/nivel-3.svg',
                'resources/images/portal/niveles/nivel-3-bloqueado.svg',
                'resources/images/portal/niveles/nivel-4.svg',
                'resources/images/portal/niveles/nivel-4-bloqueado.svg',
                'resources/images/portal/niveles/nivel-5.svg',
                'resources/images/portal/niveles/nivel-5-bloqueado.svg',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
