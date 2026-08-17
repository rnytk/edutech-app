@props(['actividad', 'deshabilitada' => false])

<div class="space-y-4">
    <label for="respuesta-directa" class="block text-xl leading-8 font-extrabold text-[#012562] sm:text-2xl">
        {{ $actividad['pregunta'] }}
    </label>
    <textarea
        id="respuesta-directa"
        wire:model="respuestaDirecta"
        rows="5"
        maxlength="10000"
        @disabled($deshabilitada)
        class="w-full resize-y rounded-2xl border-2 border-[#023C90]/20 bg-white px-4 py-3 text-[#012562] shadow-inner outline-none transition placeholder:text-slate-400 focus:border-[#023C90] focus:ring-4 focus:ring-[#FFD629]/40 disabled:cursor-not-allowed disabled:bg-slate-100"
        placeholder="Escribe aquí tu respuesta…"
    ></textarea>
</div>
