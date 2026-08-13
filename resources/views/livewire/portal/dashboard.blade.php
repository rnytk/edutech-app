<section class="w-full">
    <div class="rounded-[2rem] bg-[#012562] px-6 py-8 text-white shadow-xl sm:px-10 sm:py-10">
        <p class="text-sm font-bold tracking-[0.18em] text-[#FFD629] uppercase">Portal del estudiante</p>
        <h1 class="mt-3 text-3xl font-extrabold sm:text-4xl">¡Hola, {{ $usuario->nombre }}!</h1>
        <p class="mt-3 max-w-2xl text-base leading-7 text-white/80 sm:text-lg">
            Continúa aprendiendo sobre educación financiera a tu ritmo.
        </p>
    </div>

    <div class="mt-10 flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-sm font-bold tracking-[0.16em] text-[#023C90] uppercase">Tu aprendizaje</p>
            <h2 class="mt-1 text-2xl font-extrabold text-[#012562] sm:text-3xl">Cursos disponibles</h2>
        </div>
        <p class="text-sm font-semibold text-[#012562]/60">
            {{ $tarjetas->count() }} {{ $tarjetas->count() === 1 ? 'curso asignado' : 'cursos asignados' }}
        </p>
    </div>

    @if ($tarjetas->isEmpty())
        <div class="mt-8 rounded-[2rem] border-2 border-dashed border-[#023C90]/20 bg-white px-6 py-14 text-center shadow-sm">
            <div class="mx-auto grid size-20 place-items-center rounded-full bg-[#FFD629]/25 text-4xl" aria-hidden="true">📚</div>
            <h2 class="mt-6 text-2xl font-extrabold text-[#012562]">Aún no tienes cursos asignados</h2>
            <p class="mx-auto mt-3 max-w-lg leading-7 text-[#012562]/65">
                Cuando tu colegio habilite un curso, aparecerá aquí automáticamente.
            </p>
        </div>
    @else
        <div class="mt-8 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($tarjetas as $tarjeta)
                @php($resumen = $tarjeta['resumen'])
                <article data-curso-id="{{ $tarjeta['curso']->getKey() }}" class="group flex min-h-full flex-col overflow-hidden rounded-[2rem] bg-white shadow-[0_12px_30px_rgba(1,37,98,0.13)] ring-1 ring-[#012562]/5 transition hover:-translate-y-1 hover:shadow-[0_18px_36px_rgba(1,37,98,0.18)]">
                    <div class="relative grid h-52 place-items-center overflow-hidden bg-[#023C90] p-6">
                        <div class="absolute -top-12 -right-8 size-40 rounded-full bg-[#FFD629]/20"></div>
                        <div class="absolute -bottom-20 -left-12 size-52 rounded-full bg-white/10"></div>
                        <img
                            src="{{ $tarjeta['imagen'] }}"
                            alt="Imagen del curso {{ $tarjeta['curso']->titulo }}"
                            class="relative h-40 w-full object-contain drop-shadow-xl"
                        >
                    </div>

                    <div class="flex flex-1 flex-col p-6">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <h3 class="text-xl font-extrabold text-[#012562]">{{ $tarjeta['curso']->titulo }}</h3>
                            <span @class([
                                'rounded-full px-3 py-1 text-xs font-bold',
                                'bg-emerald-100 text-emerald-700' => $resumen['completado'],
                                'bg-[#FFD629]/30 text-[#012562]' => ! $resumen['completado'] && $resumen['total'] > 0,
                                'bg-slate-100 text-slate-600' => $resumen['total'] === 0,
                            ])>
                                @if ($resumen['completado'])
                                    Completado
                                @elseif ($resumen['total'] === 0)
                                    En preparación
                                @elseif ($resumen['completados'] > 0)
                                    En progreso
                                @else
                                    Disponible
                                @endif
                            </span>
                        </div>

                        @if ($tarjeta['descripcion'] !== '')
                            <p class="mt-3 line-clamp-3 text-sm leading-6 text-[#012562]/65">{{ $tarjeta['descripcion'] }}</p>
                        @endif

                        <div class="mt-6">
                            <div class="flex items-center justify-between gap-3 text-sm font-semibold text-[#012562]">
                                <span>{{ $resumen['completados'] }} de {{ $resumen['total'] }} módulos</span>
                                <span>{{ $resumen['porcentaje'] }}%</span>
                            </div>
                            <div
                                class="mt-2 h-3 overflow-hidden rounded-full bg-[#F0F0F0]"
                                role="progressbar"
                                aria-label="Progreso de {{ $tarjeta['curso']->titulo }}"
                                aria-valuemin="0"
                                aria-valuemax="100"
                                aria-valuenow="{{ $resumen['porcentaje'] }}"
                            >
                                <div class="h-full rounded-full bg-[#FFD629] transition-all" style="width: {{ $resumen['porcentaje'] }}%"></div>
                            </div>
                        </div>

                        <a
                            href="{{ route('cursos.bienvenida', $tarjeta['curso']) }}"
                            class="mt-7 inline-flex min-h-12 items-center justify-center gap-3 rounded-full bg-[#012562] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#023C90] focus-visible:outline-3 focus-visible:outline-offset-3 focus-visible:outline-[#FFD629]"
                            wire:navigate
                        >
                            {{ $resumen['total'] > 0 ? 'Ingresar al curso' : 'Ver información' }}
                            <span class="grid size-7 place-items-center rounded-full bg-[#FFD629] text-[#012562]" aria-hidden="true">›</span>
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</section>
