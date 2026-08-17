<?php

namespace App\Livewire\Portal;

use App\Models\Modulo;
use App\Models\Usuario;
use App\Services\ServicioCalificacion;
use App\Services\ServicioEstadoModulo;
use App\Services\ServicioProgreso;
use DomainException;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.portal-estudiante', ['inmersivo' => true])]
class VisorModulo extends Component
{
    #[Locked]
    public int $moduloId;

    public string $seccion = 'contenido';

    public int $indiceActividad = 0;

    public string $respuestaFalsoVerdadero = '';

    public string $respuestaOpcion = '';

    public string $respuestaDirecta = '';

    /** @var array<int, string> */
    public array $respuestaOrdenacion = [];

    /** @var array<string, string> */
    public array $asignacionesClasificacion = [];

    public ?bool $resultadoCorrecto = null;

    public string $mensajeFeedback = '';

    public bool $actividadSuperada = false;

    public bool $moduloCompletado = false;

    public bool $cursoCompletado = false;

    #[Locked]
    public string $actividadPreparadaUuid = '';

    public function mount(Modulo $modulo, ServicioEstadoModulo $servicioEstado): void
    {
        Gate::authorize('view', $modulo);

        $this->moduloId = $modulo->getKey();
        $usuario = $this->obtenerUsuario();
        $modulo = $this->obtenerModuloAutorizado();
        $this->actualizarFinalizacion($usuario, $modulo);

        if ($this->moduloCompletado) {
            $this->seccion = 'cierre';

            return;
        }

        $this->indiceActividad = $servicioEstado->obtenerIndicePrimeraPendiente($usuario, $modulo);

        if ($modulo->intentosActividades()->where('usuario_id', $usuario->getKey())->exists()) {
            $this->seccion = 'actividades';
            $this->prepararActividadActual($usuario, $modulo, $servicioEstado);
        }
    }

    public function irAActividades(ServicioEstadoModulo $servicioEstado): void
    {
        $usuario = $this->obtenerUsuario();
        $modulo = $this->obtenerModuloAutorizado();
        $this->reiniciarFeedback();
        $this->indiceActividad = $servicioEstado->obtenerIndicePrimeraPendiente($usuario, $modulo);

        if ($this->indiceActividad >= count($modulo->actividades ?? [])) {
            $this->seccion = 'cierre';

            return;
        }

        $this->seccion = 'actividades';
        $this->prepararActividadActual($usuario, $modulo, $servicioEstado);
    }

    public function enviarRespuesta(
        ServicioEstadoModulo $servicioEstado,
        ServicioCalificacion $servicioCalificacion,
    ): void {
        $usuario = $this->obtenerUsuario();
        $modulo = $this->obtenerModuloAutorizado();
        $actividad = $servicioEstado->presentarActividad($usuario, $modulo, $this->indiceActividad);

        if ($actividad === null) {
            throw ValidationException::withMessages(['respuesta' => 'La actividad solicitada no está disponible.']);
        }

        if ($actividad['completada']) {
            $this->actividadSuperada = true;
            $this->mensajeFeedback = 'Esta actividad ya está completada.';

            return;
        }

        $respuesta = $this->construirRespuesta($actividad);

        try {
            $resultado = $servicioCalificacion->calificar(
                $usuario,
                $modulo,
                $actividad['uuid'],
                $respuesta,
            );
        } catch (DomainException) {
            throw ValidationException::withMessages([
                'respuesta' => 'No pudimos procesar esta actividad. Solicita ayuda a un administrador.',
            ]);
        }

        $this->resultadoCorrecto = $resultado->correcta;
        $this->actividadSuperada = $resultado->actividadCompletada;
        $this->moduloCompletado = $resultado->moduloCompletado;
        $this->mensajeFeedback = match ($resultado->correcta) {
            true => '¡Excelente! Tu respuesta es correcta.',
            false => 'Aún no es correcto. Inténtalo nuevamente.',
            null => '¡Gracias! Tu respuesta quedó registrada.',
        };
    }

    public function continuar(ServicioEstadoModulo $servicioEstado): void
    {
        $usuario = $this->obtenerUsuario();
        $modulo = $this->obtenerModuloAutorizado();
        $this->indiceActividad = $servicioEstado->obtenerIndicePrimeraPendiente($usuario, $modulo);
        $this->reiniciarFeedback();

        if ($this->indiceActividad >= count($modulo->actividades ?? [])) {
            $this->actualizarFinalizacion($usuario, $modulo);
            $this->seccion = 'cierre';

            return;
        }

        $this->prepararActividadActual($usuario, $modulo, $servicioEstado);
    }

    public function confirmarFinalizacion(ServicioProgreso $servicioProgreso): void
    {
        $usuario = $this->obtenerUsuario();
        $modulo = $this->obtenerModuloAutorizado();
        $servicioProgreso->finalizarModulo($usuario, $modulo);
        $this->actualizarFinalizacion($usuario, $modulo, $servicioProgreso);
    }

    public function moverElementoArriba(int $indice): void
    {
        if ($indice <= 0 || ! isset($this->respuestaOrdenacion[$indice], $this->respuestaOrdenacion[$indice - 1])) {
            return;
        }

        [$this->respuestaOrdenacion[$indice - 1], $this->respuestaOrdenacion[$indice]] = [
            $this->respuestaOrdenacion[$indice],
            $this->respuestaOrdenacion[$indice - 1],
        ];
        $this->reiniciarFeedback();
    }

    public function moverElementoAbajo(int $indice): void
    {
        if ($indice < 0 || ! isset($this->respuestaOrdenacion[$indice], $this->respuestaOrdenacion[$indice + 1])) {
            return;
        }

        [$this->respuestaOrdenacion[$indice], $this->respuestaOrdenacion[$indice + 1]] = [
            $this->respuestaOrdenacion[$indice + 1],
            $this->respuestaOrdenacion[$indice],
        ];
        $this->reiniciarFeedback();
    }

    public function render(ServicioEstadoModulo $servicioEstado): View
    {
        $usuario = $this->obtenerUsuario();
        $modulo = $this->obtenerModuloAutorizado();
        $completadas = $servicioEstado->obtenerActividadesCompletadas($usuario, $modulo);
        $actividadActual = $this->seccion === 'actividades'
            ? $servicioEstado->presentarActividad($usuario, $modulo, $this->indiceActividad)
            : null;

        return view('livewire.portal.visor-modulo', [
            'modulo' => $modulo,
            'curso' => $modulo->nivel->curso,
            'bloques' => $this->prepararBloques($modulo),
            'capsulas' => $this->prepararCapsulas($modulo),
            'mensajeCierre' => $this->renderizarContenido($modulo->mensaje_cierre),
            'actividadActual' => $actividadActual,
            'actividadesCompletadas' => count($completadas),
            'totalActividades' => count($modulo->actividades ?? []),
        ]);
    }

    private function obtenerUsuario(): Usuario
    {
        $usuario = auth()->user();

        abort_unless($usuario instanceof Usuario, 403);

        return $usuario;
    }

    private function obtenerModuloAutorizado(): Modulo
    {
        $modulo = Modulo::query()
            ->with([
                'nivel.curso',
                'capsulas' => fn ($capsulas) => $capsulas
                    ->where('activo', true)
                    ->orderBy('orden')
                    ->orderBy('id'),
            ])
            ->findOrFail($this->moduloId);

        Gate::authorize('view', $modulo);

        return $modulo;
    }

    /** @param array<string, mixed> $actividad */
    private function construirRespuesta(array $actividad): mixed
    {
        return match ($actividad['tipo']) {
            'falso_verdadero' => match ($this->respuestaFalsoVerdadero) {
                'verdadero' => true,
                'falso' => false,
                default => throw ValidationException::withMessages([
                    'respuesta' => 'Selecciona Verdadero o Falso.',
                ]),
            },
            'opcion_multiple' => $this->respuestaOpcion,
            'respuesta_directa' => $this->respuestaDirecta,
            'ordenacion' => array_values($this->respuestaOrdenacion),
            'clasificacion' => $this->agruparClasificacion($actividad),
            default => throw ValidationException::withMessages([
                'respuesta' => 'El tipo de actividad no está disponible.',
            ]),
        };
    }

    /**
     * @param  array<string, mixed>  $actividad
     * @return array<string, array<int, string>>
     */
    private function agruparClasificacion(array $actividad): array
    {
        $respuesta = [];

        foreach ($actividad['categorias'] as $categoria) {
            $respuesta[$categoria['uuid']] = [];
        }

        foreach ($actividad['elementos'] as $elemento) {
            $categoriaUuid = $this->asignacionesClasificacion[$elemento['uuid']] ?? '';

            if (! array_key_exists($categoriaUuid, $respuesta)) {
                throw ValidationException::withMessages([
                    'respuesta' => 'Clasifica todos los elementos antes de comprobar.',
                ]);
            }

            $respuesta[$categoriaUuid][] = $elemento['uuid'];
        }

        return $respuesta;
    }

    private function prepararActividadActual(
        Usuario $usuario,
        Modulo $modulo,
        ServicioEstadoModulo $servicioEstado,
    ): void {
        $actividad = $servicioEstado->presentarActividad($usuario, $modulo, $this->indiceActividad);

        if ($actividad === null || $actividad['uuid'] === $this->actividadPreparadaUuid) {
            return;
        }

        $this->actividadPreparadaUuid = $actividad['uuid'];
        $this->respuestaFalsoVerdadero = '';
        $this->respuestaOpcion = '';
        $this->respuestaDirecta = '';
        $this->respuestaOrdenacion = $actividad['tipo'] === 'ordenacion'
            ? array_column($actividad['elementos'], 'uuid')
            : [];
        $this->asignacionesClasificacion = $actividad['tipo'] === 'clasificacion'
            ? array_fill_keys(array_column($actividad['elementos'], 'uuid'), '')
            : [];
    }

    private function reiniciarFeedback(): void
    {
        $this->resetValidation();
        $this->resultadoCorrecto = null;
        $this->mensajeFeedback = '';
        $this->actividadSuperada = false;
    }

    private function actualizarFinalizacion(
        Usuario $usuario,
        Modulo $modulo,
        ?ServicioProgreso $servicioProgreso = null,
    ): void {
        $servicioProgreso ??= app(ServicioProgreso::class);
        $this->moduloCompletado = $servicioProgreso->moduloEstaCompletado($usuario, $modulo);
        $this->cursoCompletado = $this->moduloCompletado
            && $servicioProgreso->cursoEstaCompletado($usuario, $modulo->nivel->curso);
    }

    /** @return array<int, array{uuid: string, titulo: string, contenido: Htmlable, imagen: string|null}> */
    private function prepararBloques(Modulo $modulo): array
    {
        $bloques = [];

        foreach ($modulo->bloques_contenido ?? [] as $bloque) {
            if (! is_array($bloque) || ($bloque['tipo'] ?? null) !== 'tarjeta') {
                continue;
            }

            $rutaImagen = $bloque['ruta_imagen'] ?? null;
            $bloques[] = [
                'uuid' => is_string($bloque['uuid'] ?? null) ? $bloque['uuid'] : '',
                'titulo' => is_string($bloque['titulo'] ?? null) ? $bloque['titulo'] : '',
                'contenido' => $this->renderizarContenido($bloque['contenido'] ?? null),
                'imagen' => is_string($rutaImagen) && $rutaImagen !== ''
                    ? Storage::disk('public')->url($rutaImagen)
                    : null,
            ];
        }

        return $bloques;
    }

    /** @return array<int, array{titulo: string, contenido: Htmlable, imagen: string|null}> */
    private function prepararCapsulas(Modulo $modulo): array
    {
        return $modulo->capsulas->map(function ($capsula): array {
            return [
                'titulo' => is_string($capsula->titulo) ? $capsula->titulo : '',
                'contenido' => $this->renderizarContenido($capsula->contenido),
                'imagen' => is_string($capsula->ruta_imagen) && $capsula->ruta_imagen !== ''
                    ? Storage::disk('public')->url($capsula->ruta_imagen)
                    : null,
            ];
        })->all();
    }

    private function renderizarContenido(mixed $contenido): Htmlable
    {
        return RichContentRenderer::make(is_string($contenido) || is_array($contenido) ? $contenido : null);
    }
}
