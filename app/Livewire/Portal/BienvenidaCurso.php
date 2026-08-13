<?php

namespace App\Livewire\Portal;

use App\Models\Curso;
use App\Models\Usuario;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.portal-estudiante', ['inmersivo' => true])]
class BienvenidaCurso extends Component
{
    #[Locked]
    public int $cursoId;

    public function mount(Curso $curso): void
    {
        Gate::authorize('view', $curso);

        $this->cursoId = $curso->getKey();
    }

    public function render(): View
    {
        $usuario = auth()->user();
        $curso = Curso::query()->findOrFail($this->cursoId);

        abort_unless($usuario instanceof Usuario, 403);
        Gate::forUser($usuario)->authorize('view', $curso);

        $tieneContenido = $curso->niveles()
            ->publicados()
            ->whereHas('modulos', fn (Builder $modulos): Builder => $modulos->publicados())
            ->exists();

        return view('livewire.portal.bienvenida-curso', [
            'curso' => $curso,
            'tituloBienvenida' => $curso->titulo_bienvenida ?: "¡Bienvenido(a) a {$curso->titulo}!",
            'contenidoBienvenida' => $this->convertirATexto((string) ($curso->contenido_bienvenida ?: $curso->descripcion)),
            'tieneContenido' => $tieneContenido,
        ]);
    }

    private function convertirATexto(string $contenido): string
    {
        $contenidoConSaltos = str_ireplace(
            ['<br>', '<br/>', '<br />', '</p>', '</li>'],
            ["\n", "\n", "\n", "\n\n", "\n"],
            $contenido,
        );

        return trim(html_entity_decode(strip_tags($contenidoConSaltos), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
}
