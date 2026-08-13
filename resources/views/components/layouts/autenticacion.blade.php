<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#012562">

        <title>{{ $title ?? 'Iniciar sesión | EduTech KATO-KI' }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxAppearance
    </head>
    <body class="min-h-dvh bg-[#012562] font-sans antialiased">
        {{ $slot }}

        @fluxScripts
    </body>
</html>
