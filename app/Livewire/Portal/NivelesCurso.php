<?php

namespace App\Livewire\Portal;

use App\Models\Curso;
use App\Models\Modulo;
use App\Models\Nivel;
use App\Models\Usuario;
use App\Services\ServicioDesbloqueo;
use App\Services\ServicioProgreso;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Vite;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.portal-estudiante', ['inmersivo' => true])]
class NivelesCurso extends Component
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

        $curso->load(['niveles' => fn ($niveles) => $niveles
            ->publicados()
            ->orderBy('orden')
            ->orderBy('id')
            ->with(['modulos' => fn ($modulos) => $modulos
                ->publicados()
                ->orderBy('orden')
                ->orderBy('id')])]);

        $niveles = $curso->niveles;
        $modulos = new Collection($niveles->flatMap->modulos->all());
        $servicioProgreso = app(ServicioProgreso::class);
        $resumenes = $servicioProgreso->resumirNiveles($usuario, $niveles);
        $modulosCompletados = $servicioProgreso->obtenerModulosCompletados($usuario, $modulos);
        $estados = app(ServicioDesbloqueo::class)->calcularEstadosCurso(
            $usuario,
            $curso,
            $niveles,
            $modulosCompletados,
        );

        $tarjetas = $niveles->values()->map(function (Nivel $nivel, int $indice) use (
            $estados,
            $modulosCompletados,
            $resumenes,
        ): array {
            $resumen = $resumenes[$nivel->getKey()];
            $desbloqueado = $estados['niveles'][$nivel->getKey()] ?? false;
            $estado = $this->resolverEstado($resumen, $desbloqueado);

            return [
                'nivel' => $nivel,
                'estado' => $estado,
                'resumen' => $resumen,
                'imagen' => $this->resolverImagenNivel($nivel, $indice, $estado === 'bloqueado'),
                'modulos' => $nivel->modulos->map(fn (Modulo $modulo): array => [
                    'modulo' => $modulo,
                    'estado' => isset($modulosCompletados[$modulo->getKey()])
                        ? 'completado'
                        : (($estados['modulos'][$modulo->getKey()] ?? false) ? 'disponible' : 'bloqueado'),
                ]),
            ];
        });

        return view('livewire.portal.niveles-curso', [
            'curso' => $curso,
            'tarjetas' => $tarjetas,
        ]);
    }

    /**
     * @param  array{completados: int, total: int, porcentaje: int, completado: bool}  $resumen
     */
    private function resolverEstado(array $resumen, bool $desbloqueado): string
    {
        if ($resumen['completado']) {
            return 'completado';
        }

        if (! $desbloqueado) {
            return 'bloqueado';
        }

        return $resumen['completados'] > 0 ? 'en_progreso' : 'disponible';
    }

    private function resolverImagenNivel(Nivel $nivel, int $indice, bool $bloqueado): string
    {
        if (is_string($nivel->ruta_imagen) && $nivel->ruta_imagen !== '') {
            return Storage::disk('public')->url($nivel->ruta_imagen);
        }

        $numero = min($indice + 1, 5);
        $sufijo = $bloqueado && $numero > 1 ? '-bloqueado' : '';

        return Vite::asset("resources/images/portal/niveles/nivel-{$numero}{$sufijo}.svg");
    }
}
