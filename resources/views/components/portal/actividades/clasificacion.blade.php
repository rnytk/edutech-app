@props(['actividad', 'deshabilitada' => false])

<fieldset class="space-y-4" @disabled($deshabilitada)>
    <legend class="text-xl leading-8 font-extrabold text-[#012562] sm:text-2xl">{{ $actividad['instruccion'] }}</legend>
    <p class="text-sm font-medium text-[#023C90]/75">Elige una categoría para cada elemento.</p>

    <div class="grid gap-3 sm:grid-cols-2">
        @foreach ($actividad['elementos'] as $elemento)
            <div wire:key="clasificacion-{{ $elemento['uuid'] }}" class="rounded-2xl border-2 border-[#023C90]/15 bg-white p-4 shadow-sm">
                <label for="categoria-{{ $elemento['uuid'] }}" class="block font-bold text-[#012562]">{{ $elemento['texto'] }}</label>
                <select
                    id="categoria-{{ $elemento['uuid'] }}"
                    wire:model="asignacionesClasificacion.{{ $elemento['uuid'] }}"
                    @disabled($deshabilitada)
                    class="mt-3 min-h-12 w-full rounded-xl border-2 border-[#023C90]/20 bg-white px-3 text-[#012562] outline-none transition focus:border-[#023C90] focus:ring-4 focus:ring-[#FFD629]/40 disabled:cursor-not-allowed disabled:bg-slate-100"
                >
                    <option value="">Selecciona una categoría</option>
                    @foreach ($actividad['categorias'] as $categoria)
                        <option value="{{ $categoria['uuid'] }}">{{ $categoria['nombre'] }}</option>
                    @endforeach
                </select>
            </div>
        @endforeach
    </div>
</fieldset>
