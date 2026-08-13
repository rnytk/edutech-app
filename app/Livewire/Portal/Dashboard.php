<?php

namespace App\Livewire\Portal;

use App\Models\Curso;
use App\Models\Usuario;
use App\Services\ServicioAccesoCursos;
use App\Services\ServicioProgreso;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.portal-estudiante')]
class Dashboard extends Component
{
    public function render(): View
    {
        $usuario = auth()->user();

        abort_unless($usuario instanceof Usuario, 403);

        $cursos = app(ServicioAccesoCursos::class)->obtenerCursosDisponibles($usuario);
        $resumenes = app(ServicioProgreso::class)->resumirCursos($usuario, $cursos);

        $tarjetas = $cursos->map(fn (Curso $curso): array => [
            'curso' => $curso,
            'descripcion' => Str::limit(trim(strip_tags((string) $curso->descripcion)), 145),
            'imagen' => $this->resolverImagenCurso($curso),
            'resumen' => $resumenes[$curso->getKey()],
        ]);

        return view('livewire.portal.dashboard', [
            'usuario' => $usuario,
            'tarjetas' => $tarjetas,
        ]);
    }

    private function resolverImagenCurso(Curso $curso): string
    {
        if (is_string($curso->ruta_imagen) && $curso->ruta_imagen !== '') {
            return Storage::disk('public')->url($curso->ruta_imagen);
        }

        return Vite::asset('resources/images/login/moneda.svg');
    }
}
