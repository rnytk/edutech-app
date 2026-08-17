@props(['actividad', 'deshabilitada' => false])

<fieldset class="space-y-4" @disabled($deshabilitada)>
    <legend class="text-xl leading-8 font-extrabold text-[#012562] sm:text-2xl">{{ $actividad['pregunta'] }}</legend>

    <div class="grid gap-3">
        @foreach ($actividad['opciones'] as $opcion)
            <label wire:key="opcion-{{ $opcion['uuid'] }}" class="flex min-h-14 cursor-pointer items-center gap-3 rounded-2xl border-2 border-[#023C90]/15 bg-white px-5 py-3 font-semibold text-[#012562] transition has-checked:border-[#023C90] has-checked:bg-[#EAF2FF] focus-within:outline-3 focus-within:outline-offset-2 focus-within:outline-[#FFD629]">
                <input type="radio" wire:model="respuestaOpcion" value="{{ $opcion['uuid'] }}" class="size-5 accent-[#023C90]">
                <span>{{ $opcion['texto'] }}</span>
            </label>
        @endforeach
    </div>
</fieldset>
