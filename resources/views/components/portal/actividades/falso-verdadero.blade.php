@props(['actividad', 'deshabilitada' => false])

<fieldset class="space-y-4" @disabled($deshabilitada)>
    <legend class="text-xl leading-8 font-extrabold text-[#012562] sm:text-2xl">{{ $actividad['pregunta'] }}</legend>

    <div class="grid gap-3 sm:grid-cols-2">
        @foreach (['verdadero' => 'Verdadero', 'falso' => 'Falso'] as $valor => $etiqueta)
            <label class="flex min-h-16 cursor-pointer items-center gap-3 rounded-2xl border-2 border-[#023C90]/15 bg-white px-5 py-4 font-bold text-[#012562] transition has-checked:border-[#023C90] has-checked:bg-[#EAF2FF] focus-within:outline-3 focus-within:outline-offset-2 focus-within:outline-[#FFD629]">
                <input type="radio" wire:model="respuestaFalsoVerdadero" value="{{ $valor }}" class="size-5 accent-[#023C90]">
                <span>{{ $etiqueta }}</span>
            </label>
        @endforeach
    </div>
</fieldset>
