@props(['actividad', 'respuesta', 'deshabilitada' => false])

<fieldset class="space-y-4" @disabled($deshabilitada)>
    <legend class="text-xl leading-8 font-extrabold text-[#012562] sm:text-2xl">{{ $actividad['instruccion'] }}</legend>
    <p class="text-sm font-medium text-[#023C90]/75">Usa los botones para colocar cada elemento en el orden que consideres correcto.</p>

    <ol class="space-y-3" aria-label="Elementos para ordenar">
        @foreach ($respuesta as $indice => $elementoUuid)
            @php($elemento = collect($actividad['elementos'])->firstWhere('uuid', $elementoUuid))
            @if ($elemento)
                <li wire:key="orden-{{ $elementoUuid }}" class="flex min-w-0 items-center gap-3 rounded-2xl border-2 border-[#023C90]/15 bg-white p-3 shadow-sm">
                    <span class="grid size-9 shrink-0 place-items-center rounded-full bg-[#023C90] font-extrabold text-white" aria-hidden="true">{{ $indice + 1 }}</span>
                    <span class="min-w-0 flex-1 font-semibold text-[#012562]">{{ $elemento['texto'] }}</span>
                    <div class="flex shrink-0 gap-2" aria-label="Mover {{ $elemento['texto'] }}">
                        <button type="button" wire:click="moverElementoArriba({{ $indice }})" @disabled($deshabilitada || $loop->first) aria-label="Mover arriba" class="grid size-11 place-items-center rounded-xl bg-[#F0F0F0] text-xl font-black text-[#023C90] transition hover:bg-[#FFD629] focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-[#023C90] disabled:cursor-not-allowed disabled:opacity-35">↑</button>
                        <button type="button" wire:click="moverElementoAbajo({{ $indice }})" @disabled($deshabilitada || $loop->last) aria-label="Mover abajo" class="grid size-11 place-items-center rounded-xl bg-[#F0F0F0] text-xl font-black text-[#023C90] transition hover:bg-[#FFD629] focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-[#023C90] disabled:cursor-not-allowed disabled:opacity-35">↓</button>
                    </div>
                </li>
            @endif
        @endforeach
    </ol>
</fieldset>
