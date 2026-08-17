<section class="relative isolate min-h-dvh w-full overflow-hidden bg-[#012562] text-white">
    <img
        src="{{ Vite::asset('resources/images/login/nubes-superiores.svg') }}"
        alt=""
        aria-hidden="true"
        class="pointer-events-none absolute -top-1 left-1/2 z-0 w-[190%] max-w-none -translate-x-1/2 sm:w-[135%] lg:w-[72%]"
    >
    <img
        src="{{ Vite::asset('resources/images/login/nubes-inferiores.svg') }}"
        alt=""
        aria-hidden="true"
        class="pointer-events-none absolute -bottom-1 left-1/2 z-0 w-[175%] max-w-none -translate-x-1/2 sm:w-[120%] lg:left-[58%] lg:w-[72%]"
    >

    <a
        href="{{ route('cursos.bienvenida', $curso) }}"
        aria-label="Volver a la bienvenida del curso"
        class="absolute top-5 left-5 z-30 grid size-13 place-items-center rounded-2xl bg-[#FFD629] shadow-lg transition hover:-translate-x-1 focus-visible:outline-3 focus-visible:outline-offset-3 focus-visible:outline-white sm:top-8 sm:left-9 sm:size-16"
        wire:navigate
    >
        <img src="{{ Vite::asset('resources/images/portal/icono-retroceso.svg') }}" alt="" aria-hidden="true" class="size-9 sm:size-11">
    </a>

    <img
        src="{{ Vite::asset('resources/images/login/logo-katoki.png') }}"
        alt="Cooperativa KATO-KI R.L."
        class="absolute top-5 right-5 z-20 h-auto w-36 object-contain sm:top-7 sm:right-8 sm:w-44 lg:top-8 lg:right-[4vw] lg:w-[clamp(11rem,13vw,15rem)]"
    >

    <div class="relative z-10 mx-auto flex min-h-dvh w-full max-w-[1800px] flex-col px-5 pt-28 pb-36 sm:px-9 sm:pt-32 lg:px-[5vw] lg:pt-40">
        <div class="text-center">
            <p class="text-sm font-bold tracking-[0.2em] text-[#FFD629] uppercase">{{ $curso->titulo }}</p>
            <h1 class="mt-3 text-4xl font-extrabold tracking-wide sm:text-5xl">APRENDE</h1>
        </div>

        @if ($tarjetas->isEmpty())
            <div class="mx-auto mt-16 max-w-xl rounded-[2rem] bg-white/10 px-7 py-12 text-center ring-1 ring-white/20">
                <div class="mx-auto grid size-20 place-items-center rounded-full bg-[#FFD629] text-4xl" aria-hidden="true">📖</div>
                <h2 class="mt-6 text-2xl font-extrabold">Niveles en preparación</h2>
                <p class="mt-3 leading-7 text-white/75">Este curso aún no tiene niveles publicados. Vuelve a consultarlo pronto.</p>
            </div>
        @else
            <div class="mt-12 grid items-start gap-6 sm:grid-cols-2 lg:mt-14 lg:grid-cols-3 xl:grid-cols-5">
                @foreach ($tarjetas as $tarjeta)
                    <article data-nivel-id="{{ $tarjeta['nivel']->getKey() }}" data-nivel-estado="{{ $tarjeta['estado'] }}" @class([
                        'overflow-hidden rounded-[1.4rem] bg-[#F0F0F0] text-[#012562] shadow-[0_10px_24px_rgba(0,0,0,0.24)] ring-1 ring-white/50',
                        'opacity-85' => $tarjeta['estado'] === 'bloqueado',
                    ])>
                        <div @class([
                            'px-4 py-3 text-center text-xl font-extrabold sm:text-2xl',
                            'bg-[#FFD629]' => $tarjeta['estado'] !== 'bloqueado',
                            'bg-[#C7C7C7] text-slate-700' => $tarjeta['estado'] === 'bloqueado',
                        ])>
                            {{ $tarjeta['nivel']->titulo }}
                        </div>

                        <div class="grid h-52 place-items-center bg-[#F8F8F8] p-5">
                            <img
                                src="{{ $tarjeta['imagen'] }}"
                                alt="Ilustración de {{ $tarjeta['nivel']->titulo }}"
                                @class([
                                    'max-h-40 w-full object-contain',
                                    'grayscale' => $tarjeta['estado'] === 'bloqueado' && $tarjeta['nivel']->ruta_imagen,
                                ])
                            >
                        </div>

                        <div class="min-h-44 bg-[#023C90] px-4 py-4 text-white">
                            <p class="min-h-12 text-center text-sm leading-5 font-semibold">
                                {{ $tarjeta['nivel']->descripcion ?: $tarjeta['nivel']->titulo }}
                            </p>

                            <div class="mt-3 flex items-center justify-between gap-2 text-xs font-bold">
                                <span>{{ $tarjeta['resumen']['completados'] }}/{{ $tarjeta['resumen']['total'] }} módulos</span>
                                <span class="rounded-full bg-white/15 px-2 py-1">
                                    @switch($tarjeta['estado'])
                                        @case('completado') Completado @break
                                        @case('en_progreso') En progreso @break
                                        @case('disponible') Disponible @break
                                        @default Bloqueado
                                    @endswitch
                                </span>
                            </div>

                            <div
                                class="mt-2 h-2 overflow-hidden rounded-full bg-white/20"
                                role="progressbar"
                                aria-label="Progreso de {{ $tarjeta['nivel']->titulo }}"
                                aria-valuemin="0"
                                aria-valuemax="100"
                                aria-valuenow="{{ $tarjeta['resumen']['porcentaje'] }}"
                            >
                                <div class="h-full rounded-full bg-[#FFD629]" style="width: {{ $tarjeta['resumen']['porcentaje'] }}%"></div>
                            </div>

                            @if ($tarjeta['modulos']->isEmpty())
                                <p class="mt-4 rounded-xl bg-white/10 px-3 py-2 text-center text-xs">Módulos en preparación</p>
                            @else
                                <ul class="mt-4 space-y-2" aria-label="Módulos de {{ $tarjeta['nivel']->titulo }}">
                                    @foreach ($tarjeta['modulos'] as $modulo)
                                        <li data-modulo-id="{{ $modulo['modulo']->getKey() }}" data-modulo-estado="{{ $modulo['estado'] }}" class="text-xs">
                                            @if ($modulo['estado'] === 'bloqueado')
                                                <div aria-disabled="true" class="flex min-h-10 cursor-not-allowed items-center gap-2 rounded-xl px-2 text-white/60">
                                                    <span class="size-2.5 shrink-0 rounded-full bg-white/35"></span>
                                                    <span class="min-w-0 flex-1 truncate">{{ $modulo['modulo']->titulo }}</span>
                                                    <span aria-hidden="true">🔒</span>
                                                    <span class="sr-only">Estado: bloqueado</span>
                                                </div>
                                            @else
                                                <a href="{{ route('modulos.ver', $modulo['modulo']) }}" wire:navigate class="flex min-h-10 items-center gap-2 rounded-xl px-2 transition hover:bg-white/15 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#FFD629]">
                                                    <span @class([
                                                        'size-2.5 shrink-0 rounded-full',
                                                        'bg-emerald-400' => $modulo['estado'] === 'completado',
                                                        'bg-[#FFD629]' => $modulo['estado'] === 'disponible',
                                                    ])></span>
                                                    <span class="min-w-0 flex-1 truncate">{{ $modulo['modulo']->titulo }}</span>
                                                    <span aria-hidden="true">→</span>
                                                    <span class="sr-only">Estado: {{ $modulo['estado'] }}</span>
                                                </a>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
