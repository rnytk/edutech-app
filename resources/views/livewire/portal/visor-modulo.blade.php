<section class="relative isolate min-h-dvh w-full overflow-x-hidden bg-[#F0F0F0] text-[#012562]">
    <div class="absolute inset-x-0 top-0 h-72 bg-[#012562]" aria-hidden="true"></div>
    <img src="{{ Vite::asset('resources/images/login/nubes-superiores.svg') }}" alt="" aria-hidden="true" class="pointer-events-none absolute top-0 left-1/2 w-[180%] max-w-none -translate-x-1/2 opacity-70 sm:w-[110%] lg:w-[72%]">

    <header class="relative z-10 mx-auto flex w-full max-w-7xl items-center justify-between gap-4 px-4 py-5 sm:px-8 sm:py-7">
        <a
            href="{{ route('cursos.niveles', $curso) }}"
            aria-label="Volver a los niveles de {{ $curso->titulo }}"
            class="grid size-12 shrink-0 place-items-center rounded-2xl bg-[#FFD629] shadow-lg transition hover:-translate-x-1 focus-visible:outline-3 focus-visible:outline-offset-3 focus-visible:outline-white sm:size-14"
            wire:navigate
        >
            <img src="{{ Vite::asset('resources/images/portal/icono-retroceso.svg') }}" alt="" aria-hidden="true" class="size-8 sm:size-10">
        </a>

        <div class="min-w-0 text-center text-white">
            <p class="truncate text-xs font-bold tracking-[0.16em] text-[#FFD629] uppercase sm:text-sm">{{ $modulo->nivel->titulo }}</p>
            <h1 class="mt-1 line-clamp-2 text-xl font-extrabold sm:text-3xl">{{ $modulo->titulo }}</h1>
        </div>

        <img src="{{ Vite::asset('resources/images/login/logo-katoki.png') }}" alt="Cooperativa KATO-KI R.L." class="h-auto w-24 shrink-0 object-contain sm:w-36">
    </header>

    <div class="relative z-10 mx-auto w-full max-w-5xl px-4 pb-14 sm:px-8 sm:pb-20">
        <div class="mb-5 rounded-2xl bg-white/10 p-3 ring-1 ring-white/20 backdrop-blur sm:p-4">
            <div class="flex items-center justify-between gap-3 text-xs font-bold text-white sm:text-sm">
                <span>Progreso del módulo</span>
                <span>{{ $actividadesCompletadas }} de {{ $totalActividades }} actividades</span>
            </div>
            <div class="mt-2 h-3 overflow-hidden rounded-full bg-white/20" role="progressbar" aria-label="Progreso de actividades" aria-valuemin="0" aria-valuemax="{{ max($totalActividades, 1) }}" aria-valuenow="{{ $actividadesCompletadas }}">
                <div class="h-full rounded-full bg-[#FFD629] transition-all duration-500" style="width: {{ $totalActividades > 0 ? round(($actividadesCompletadas / $totalActividades) * 100) : 0 }}%"></div>
            </div>
        </div>

        @if ($seccion === 'contenido')
            <div class="space-y-6" data-seccion="contenido">
                @forelse ($bloques as $indice => $bloque)
                    <article class="overflow-hidden rounded-[2rem] bg-white shadow-[0_18px_50px_rgba(1,37,98,0.18)] ring-1 ring-[#012562]/5">
                        @if ($bloque['imagen'])
                            <img src="{{ $bloque['imagen'] }}" alt="" class="max-h-80 w-full bg-[#EAF2FF] object-contain p-5 sm:p-8">
                        @endif
                        <div class="p-6 sm:p-9">
                            <p class="text-xs font-extrabold tracking-[0.16em] text-[#023C90]/60 uppercase">Tarjeta {{ $indice + 1 }}</p>
                            <h2 class="mt-2 text-2xl font-extrabold text-[#012562] sm:text-3xl">{{ $bloque['titulo'] }}</h2>
                            <div class="mt-5 max-w-none leading-7 text-slate-700 [&_a]:font-semibold [&_a]:text-[#023C90] [&_a]:underline [&_blockquote]:border-l-4 [&_blockquote]:border-[#FFD629] [&_blockquote]:pl-4 [&_h2]:mt-5 [&_h2]:text-xl [&_h2]:font-extrabold [&_h3]:mt-4 [&_h3]:text-lg [&_h3]:font-bold [&_li]:mb-1 [&_ol]:my-3 [&_ol]:list-decimal [&_ol]:pl-6 [&_p]:mb-3 [&_strong]:font-extrabold [&_ul]:my-3 [&_ul]:list-disc [&_ul]:pl-6">{{ $bloque['contenido'] }}</div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-[2rem] bg-white p-8 text-center shadow-xl">
                        <h2 class="text-2xl font-extrabold">Contenido en preparación</h2>
                        <p class="mt-3 text-slate-600">Puedes continuar con las actividades disponibles.</p>
                    </div>
                @endforelse

                @foreach ($capsulas as $capsula)
                    <aside class="overflow-hidden rounded-[2rem] border-4 border-[#FFD629] bg-[#FFF9D8] shadow-lg" aria-label="Sabías que">
                        <div class="grid gap-5 p-6 sm:grid-cols-[1fr_auto] sm:items-center sm:p-8">
                            <div>
                                <p class="text-sm font-black tracking-[0.12em] text-[#023C90] uppercase">¿Sabías que?</p>
                                @if ($capsula['titulo'])
                                    <h2 class="mt-2 text-xl font-extrabold sm:text-2xl">{{ $capsula['titulo'] }}</h2>
                                @endif
                                <div class="mt-3 max-w-none leading-7 text-slate-700 [&_a]:font-semibold [&_a]:text-[#023C90] [&_a]:underline [&_li]:mb-1 [&_ol]:my-3 [&_ol]:list-decimal [&_ol]:pl-6 [&_p]:mb-3 [&_strong]:font-extrabold [&_ul]:my-3 [&_ul]:list-disc [&_ul]:pl-6">{{ $capsula['contenido'] }}</div>
                            </div>
                            @if ($capsula['imagen'])
                                <img src="{{ $capsula['imagen'] }}" alt="" class="mx-auto max-h-36 w-40 object-contain">
                            @endif
                        </div>
                    </aside>
                @endforeach

                <div class="flex justify-center pt-2">
                    <button type="button" wire:click="irAActividades" wire:loading.attr="disabled" class="min-h-14 rounded-full bg-[#FFD629] px-8 py-3 text-base font-extrabold text-[#012562] shadow-[0_8px_0_#d9ad00] transition hover:-translate-y-0.5 focus-visible:outline-3 focus-visible:outline-offset-4 focus-visible:outline-[#023C90] disabled:cursor-wait disabled:opacity-70">
                        {{ $totalActividades > 0 ? 'Comenzar actividades' : 'Finalizar módulo' }}
                    </button>
                </div>
            </div>
        @elseif ($seccion === 'actividades' && $actividadActual)
            <article class="rounded-[2rem] bg-white p-5 shadow-[0_18px_50px_rgba(1,37,98,0.2)] ring-1 ring-[#012562]/5 sm:p-9" data-seccion="actividades" data-tipo-actividad="{{ $actividadActual['tipo'] }}">
                <div class="mb-7 flex flex-wrap items-center justify-between gap-3">
                    <span class="rounded-full bg-[#EAF2FF] px-4 py-2 text-xs font-extrabold tracking-wide text-[#023C90] uppercase">Actividad {{ $indiceActividad + 1 }} de {{ $totalActividades }}</span>
                    @if ($actividadActual['completada'])
                        <span class="rounded-full bg-emerald-100 px-4 py-2 text-xs font-extrabold text-emerald-800">Completada</span>
                    @endif
                </div>

                <form wire:submit="enviarRespuesta" class="space-y-7">
                    @switch($actividadActual['tipo'])
                        @case('falso_verdadero')
                            <x-portal.actividades.falso-verdadero :actividad="$actividadActual" :deshabilitada="$actividadSuperada" />
                            @break
                        @case('opcion_multiple')
                            <x-portal.actividades.opcion-multiple :actividad="$actividadActual" :deshabilitada="$actividadSuperada" />
                            @break
                        @case('respuesta_directa')
                            <x-portal.actividades.respuesta-directa :actividad="$actividadActual" :deshabilitada="$actividadSuperada" />
                            @break
                        @case('ordenacion')
                            <x-portal.actividades.ordenacion :actividad="$actividadActual" :respuesta="$respuestaOrdenacion" :deshabilitada="$actividadSuperada" />
                            @break
                        @case('clasificacion')
                            <x-portal.actividades.clasificacion :actividad="$actividadActual" :deshabilitada="$actividadSuperada" />
                            @break
                    @endswitch

                    @error('respuesta')
                        <p role="alert" class="rounded-2xl bg-red-50 px-4 py-3 font-semibold text-red-800 ring-1 ring-red-200">{{ $message }}</p>
                    @enderror

                    @if ($mensajeFeedback !== '')
                        <div role="status" @class([
                            'rounded-2xl px-5 py-4 font-bold ring-1',
                            'bg-emerald-50 text-emerald-800 ring-emerald-200' => $actividadSuperada,
                            'bg-amber-50 text-amber-900 ring-amber-200' => ! $actividadSuperada,
                        ])>
                            {{ $mensajeFeedback }}
                        </div>
                    @endif

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        @if ($actividadSuperada || $actividadActual['completada'])
                            <button type="button" wire:click="continuar" wire:loading.attr="disabled" class="min-h-13 rounded-full bg-[#FFD629] px-7 py-3 font-extrabold text-[#012562] shadow-md transition hover:bg-[#ffe36b] focus-visible:outline-3 focus-visible:outline-offset-3 focus-visible:outline-[#023C90]">
                                Continuar
                            </button>
                        @else
                            <button type="submit" wire:loading.attr="disabled" wire:target="enviarRespuesta" class="min-h-13 rounded-full bg-[#023C90] px-7 py-3 font-extrabold text-white shadow-md transition hover:bg-[#012562] focus-visible:outline-3 focus-visible:outline-offset-3 focus-visible:outline-[#FFD629] disabled:cursor-wait disabled:opacity-70">
                                <span wire:loading.remove wire:target="enviarRespuesta">Comprobar respuesta</span>
                                <span wire:loading wire:target="enviarRespuesta">Comprobando…</span>
                            </button>
                        @endif
                    </div>
                </form>
            </article>
        @else
            <article class="rounded-[2rem] bg-white p-7 text-center shadow-[0_18px_50px_rgba(1,37,98,0.2)] sm:p-12" data-seccion="cierre">
                <div class="mx-auto grid size-24 place-items-center rounded-full bg-[#FFD629] text-5xl shadow-lg" aria-hidden="true">★</div>
                <p class="mt-6 text-sm font-extrabold tracking-[0.16em] text-[#023C90] uppercase">Módulo completado</p>
                <h2 class="mt-2 text-3xl font-black text-[#012562] sm:text-4xl">¡Lo lograste!</h2>
                <div class="mx-auto mt-5 max-w-2xl leading-7 text-slate-700 [&_a]:font-semibold [&_a]:text-[#023C90] [&_a]:underline [&_li]:mb-1 [&_ol]:my-3 [&_ol]:list-decimal [&_ol]:pl-6 [&_p]:mb-3 [&_strong]:font-extrabold [&_ul]:my-3 [&_ul]:list-disc [&_ul]:pl-6">{{ $mensajeCierre }}</div>

                @if ($cursoCompletado)
                    <div class="mx-auto mt-7 max-w-xl rounded-2xl bg-[#FFF9D8] px-5 py-4 font-bold text-[#012562] ring-2 ring-[#FFD629]">
                        ¡Completaste todo el curso! La opción de diploma estará disponible próximamente.
                    </div>
                @endif

                @unless ($moduloCompletado)
                    <button type="button" wire:click="confirmarFinalizacion" wire:loading.attr="disabled" class="mt-8 min-h-14 rounded-full bg-[#FFD629] px-8 py-3 font-extrabold text-[#012562] shadow-[0_8px_0_#d9ad00] transition hover:-translate-y-0.5 focus-visible:outline-3 focus-visible:outline-offset-4 focus-visible:outline-[#023C90]">
                        Completar módulo
                    </button>
                @else
                    <a href="{{ route('cursos.niveles', $curso) }}" wire:navigate class="mt-8 inline-flex min-h-14 items-center justify-center rounded-full bg-[#FFD629] px-8 py-3 font-extrabold text-[#012562] shadow-[0_8px_0_#d9ad00] transition hover:-translate-y-0.5 focus-visible:outline-3 focus-visible:outline-offset-4 focus-visible:outline-[#023C90]">
                        Continuar aprendiendo
                    </a>
                @endunless
            </article>
        @endif
    </div>
</section>
