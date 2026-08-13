<main class="relative isolate min-h-dvh overflow-hidden bg-[#012562]">
    <img
        src="{{ Vite::asset('resources/images/login/nubes-superiores.svg') }}"
        alt=""
        aria-hidden="true"
        class="pointer-events-none absolute -top-1 left-0 z-0 w-[150%] max-w-none sm:w-full lg:w-[68%]"
    >
    <img
        src="{{ Vite::asset('resources/images/login/nubes-inferiores.svg') }}"
        alt=""
        aria-hidden="true"
        class="pointer-events-none absolute -bottom-1 left-1/2 z-0 w-[160%] max-w-none -translate-x-1/2 sm:w-[115%] lg:left-[58%] lg:w-[72%]"
    >

    <img
        src="{{ Vite::asset('resources/images/login/logo-katoki.png') }}"
        alt="Cooperativa KATO-KI R.L."
        class="absolute top-5 right-5 z-20 h-auto w-36 object-contain sm:top-7 sm:right-8 sm:w-44 lg:top-8 lg:right-[4vw] lg:w-[clamp(11rem,13vw,15rem)]"
    >

    <div class="relative z-10 mx-auto grid min-h-dvh w-full max-w-[1680px] grid-cols-1 items-center gap-6 px-5 pt-24 pb-28 sm:px-10 sm:pt-28 lg:grid-cols-[1.08fr_0.92fr] lg:gap-12 lg:px-[5vw] lg:pt-24 lg:pb-24">
        <section class="flex min-w-0 flex-col items-center justify-center pt-5 text-center lg:items-start lg:pt-8 lg:text-left">
            <h1 class="font-display text-[clamp(3.2rem,10vw,5.6rem)] leading-[0.9] tracking-wide text-white drop-shadow-sm lg:whitespace-nowrap lg:text-[clamp(3.5rem,5vw,5.8rem)]">
                EduTech KATO-KI
            </h1>

            <img
                src="{{ Vite::asset('resources/images/login/moneda.svg') }}"
                alt="Moneda animada de EduTech KATO-KI"
                class="mt-4 w-[min(75vw,24rem)] object-contain drop-shadow-[0_16px_18px_rgba(0,0,0,0.12)] sm:mt-5 sm:w-[22rem] lg:mt-6 lg:w-[min(45vw,40rem)]"
            >
        </section>

        <section class="flex w-full justify-center lg:justify-end">
            <div class="w-full max-w-[34rem] rounded-[2rem] bg-[#F0F0F0] px-6 py-9 shadow-[0_10px_26px_rgba(0,0,0,0.38)] sm:px-12 sm:py-12 lg:min-h-[31rem] lg:px-14 lg:py-14">
                <h2 class="text-center text-2xl font-bold text-[#012562] sm:text-3xl lg:text-[2rem]">
                    Ingresa a tu cuenta
                </h2>

                <form method="POST" action="{{ route('estudiante.login.autenticar') }}" class="mt-9 space-y-7 sm:mt-11 sm:space-y-8">
                    @csrf

                    <div>
                        <label for="correo_electronico" class="mb-2 block pl-3 text-base font-medium text-[#012562] sm:text-lg">
                            Usuario
                        </label>
                        <div class="relative">
                            <img
                                src="{{ Vite::asset('resources/images/login/icono-usuario.svg') }}"
                                alt=""
                                aria-hidden="true"
                                class="pointer-events-none absolute top-1/2 left-4 h-7 w-8 -translate-y-1/2 object-contain"
                            >
                            <input
                                id="correo_electronico"
                                name="correo_electronico"
                                type="email"
                                value="{{ old('correo_electronico') }}"
                                autocomplete="email"
                                inputmode="email"
                                required
                                autofocus
                                aria-invalid="{{ $errors->has('correo_electronico') ? 'true' : 'false' }}"
                                aria-describedby="{{ $errors->has('correo_electronico') ? 'error-inicio-sesion' : '' }}"
                                placeholder="correo@ejemplo.com"
                                class="h-14 w-full rounded-full border border-transparent bg-white pr-5 pl-14 text-base text-[#012562] shadow-[0_5px_12px_rgba(1,37,98,0.12)] outline-none transition placeholder:text-[#012562]/45 focus:border-[#FFD629] focus:ring-4 focus:ring-[#FFD629]/25 sm:h-16 sm:text-lg"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="contrasena" class="mb-2 block pl-3 text-base font-medium text-[#012562] sm:text-lg">
                            Contraseña
                        </label>
                        <div class="relative">
                            <img
                                src="{{ Vite::asset('resources/images/login/icono-contrasena.svg') }}"
                                alt=""
                                aria-hidden="true"
                                class="pointer-events-none absolute top-1/2 left-4 h-7 w-8 -translate-y-1/2 object-contain"
                            >
                            <input
                                id="contrasena"
                                name="contrasena"
                                type="password"
                                autocomplete="current-password"
                                required
                                class="h-14 w-full rounded-full border border-transparent bg-white pr-5 pl-14 text-base text-[#012562] shadow-[0_5px_12px_rgba(1,37,98,0.12)] outline-none transition focus:border-[#FFD629] focus:ring-4 focus:ring-[#FFD629]/25 sm:h-16 sm:text-lg"
                            >
                        </div>
                    </div>

                    @if ($errors->any())
                        <p id="error-inicio-sesion" role="alert" class="rounded-2xl bg-red-50 px-4 py-3 text-center text-sm font-medium text-red-700">
                            {{ $errors->first() }}
                        </p>
                    @endif

                    <div class="flex justify-center pt-2 sm:pt-4">
                        <button
                            type="submit"
                            class="group inline-flex min-h-12 items-center gap-3 rounded-full bg-white py-2 pr-2 pl-7 text-base font-bold text-[#012562] shadow-[0_5px_12px_rgba(1,37,98,0.16)] transition hover:-translate-y-0.5 hover:shadow-[0_8px_16px_rgba(1,37,98,0.2)] focus-visible:outline-3 focus-visible:outline-offset-3 focus-visible:outline-[#FFD629] sm:text-lg"
                        >
                            Ingresar
                            <span class="grid size-9 place-items-center rounded-full bg-[#FFD629] transition group-hover:bg-[#ffe36b]" aria-hidden="true">
                                <img src="{{ Vite::asset('resources/images/login/icono-ingreso.svg') }}" alt="" class="h-7 w-7 object-contain">
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</main>
