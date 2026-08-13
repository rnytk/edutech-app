<section class="m-auto w-full max-w-3xl rounded-[2rem] bg-white p-7 text-center shadow-xl sm:p-12">
    <div class="mx-auto grid size-16 place-items-center rounded-full bg-[#FFD629] text-3xl" aria-hidden="true">✓</div>
    <p class="mt-6 text-sm font-bold tracking-[0.18em] text-[#023C90] uppercase">Portal del estudiante</p>
    <h1 class="mt-3 text-3xl font-extrabold text-[#012562] sm:text-4xl">
        ¡Bienvenido, {{ auth()->user()->nombre }}!
    </h1>
    <p class="mx-auto mt-4 max-w-xl text-base leading-7 text-[#012562]/70 sm:text-lg">
        Tu espacio de aprendizaje está listo. El contenido educativo se incorporará en la siguiente etapa.
    </p>
</section>
