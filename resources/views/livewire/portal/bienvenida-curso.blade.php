<section class="relative isolate min-h-dvh w-full overflow-hidden bg-[#012562] text-white">
    <img
        src="{{ Vite::asset('resources/images/login/nubes-superiores.svg') }}"
        alt=""
        aria-hidden="true"
        class="pointer-events-none absolute -top-1 left-0 z-0 w-[170%] max-w-none sm:w-[120%] lg:w-[68%]"
    >
    <img
        src="{{ Vite::asset('resources/images/login/nubes-inferiores.svg') }}"
        alt=""
        aria-hidden="true"
        class="pointer-events-none absolute -bottom-1 left-1/2 z-0 w-[175%] max-w-none -translate-x-1/2 sm:w-[120%] lg:left-[38%] lg:w-[72%]"
    >
    <img
        src="{{ Vite::asset('resources/images/login/logo-katoki.png') }}"
        alt="Cooperativa KATO-KI R.L."
        class="absolute top-5 right-5 z-20 h-auto w-36 object-contain sm:top-7 sm:right-8 sm:w-44 lg:top-8 lg:right-[4vw] lg:w-[clamp(11rem,13vw,15rem)]"
    >

    <div class="relative z-10 mx-auto grid min-h-dvh w-full max-w-[1720px] grid-cols-1 items-center gap-8 px-6 pt-28 pb-32 sm:px-12 lg:grid-cols-[1.28fr_0.72fr] lg:gap-10 lg:px-[5.5vw] lg:pt-28 lg:pb-24">
        <div class="order-2 text-center lg:order-1 lg:text-left">
            <p class="text-sm font-bold tracking-[0.2em] text-[#FFD629] uppercase">{{ $curso->titulo }}</p>
            <h1 class="mt-4 text-[clamp(3rem,8vw,6rem)] leading-[0.95] font-extrabold text-white lg:max-w-[58rem] lg:text-[clamp(3.8rem,4.2vw,5.2rem)]">
                {{ $tituloBienvenida }}
            </h1>

            @if ($contenidoBienvenida !== '')
                <p class="mx-auto mt-7 max-w-3xl whitespace-pre-line text-base leading-7 text-white/90 sm:text-xl sm:leading-8 lg:mx-0 lg:text-2xl lg:leading-9">
                    {{ $contenidoBienvenida }}
                </p>
            @endif

            @if ($tieneContenido)
                <a
                    href="{{ route('cursos.niveles', $curso) }}"
                    class="mt-9 inline-flex min-h-13 items-center gap-4 rounded-full bg-white py-2 pr-2 pl-7 text-lg font-extrabold text-[#012562] shadow-[0_7px_15px_rgba(255,255,255,0.28)] transition hover:-translate-y-0.5 focus-visible:outline-3 focus-visible:outline-offset-4 focus-visible:outline-[#FFD629]"
                    wire:navigate
                >
                    Comenzar
                    <span class="grid size-10 place-items-center rounded-full bg-[#FFD629] text-3xl leading-none" aria-hidden="true">›</span>
                </a>
            @else
                <div class="mt-9 inline-flex max-w-xl flex-col items-center gap-3 rounded-3xl bg-white/10 px-6 py-5 text-center ring-1 ring-white/20 lg:items-start lg:text-left">
                    <p class="font-bold text-[#FFD629]">Contenido en preparación</p>
                    <p class="text-sm leading-6 text-white/80">Este curso todavía no tiene niveles y módulos publicados. Vuelve a consultarlo pronto.</p>
                    <a href="{{ route('portal.inicio') }}" class="text-sm font-bold underline decoration-[#FFD629] decoration-2 underline-offset-4" wire:navigate>Regresar a mis cursos</a>
                </div>
            @endif
        </div>

       <div class="order-1 flex justify-center pt-7 lg:order-2 lg:justify-start lg:pt-20">
    <img
        src="{{ Vite::asset('resources/images/portal/icono-bienvenida.svg') }}"
        alt="Personaje de bienvenida de EduTech KATO-KI"
        class="w-[min(72vw,24rem)] object-contain drop-shadow-[0_18px_20px_rgba(0,0,0,0.14)] sm:w-[25rem] lg:w-[42rem] lg:max-w-none lg:-translate-x-8"
    >
</div>
    </div>
</section>
