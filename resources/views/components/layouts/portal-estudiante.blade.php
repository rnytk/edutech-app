<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#012562">

        <title>{{ $title ?? 'Portal del estudiante | EduTech KATO-KI' }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxAppearance
    </head>
    <body class="min-h-dvh bg-[#F0F0F0] font-sans text-[#012562] antialiased">
        <div class="flex min-h-dvh flex-col">
            <header class="bg-[#012562] text-white shadow-lg">
                <div class="mx-auto flex w-full max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                    <a href="{{ route('portal.inicio') }}" class="flex min-w-0 items-center gap-3 rounded-lg focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#FFD629]" wire:navigate>
                        <img
                            src="{{ Vite::asset('resources/images/login/logo-katoki.png') }}"
                            alt="Cooperativa KATO-KI R.L."
                            class="h-11 w-24 shrink-0 object-contain object-left sm:h-12 sm:w-28"
                        >
                        <span class="truncate font-display text-2xl tracking-wide sm:text-3xl">EduTech KATO-KI</span>
                    </a>

                    <div class="flex items-center gap-3 sm:gap-5">
                        <nav aria-label="Navegación principal">
                            <a
                                href="{{ route('portal.inicio') }}"
                                aria-current="page"
                                class="rounded-full bg-white/10 px-4 py-2 text-sm font-semibold transition hover:bg-white/20 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#FFD629]"
                                wire:navigate
                            >
                                Inicio
                            </a>
                        </nav>

                        <form method="POST" action="{{ route('estudiante.logout') }}">
                            @csrf
                            <button
                                type="submit"
                                class="min-h-11 rounded-full bg-[#FFD629] px-4 py-2 text-sm font-bold text-[#012562] shadow-md transition hover:bg-[#ffe36b] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                            >
                                Cerrar sesión
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="mx-auto flex w-full max-w-7xl flex-1 px-4 py-8 sm:px-6 sm:py-10 lg:px-8">
                {{ $slot }}
            </main>
        </div>

        @fluxScripts
    </body>
</html>
