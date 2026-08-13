<?php

namespace App\Services;

use App\Enums\TipoActividad;
use App\Models\IntentoActividad;
use App\Models\Modulo;
use App\Models\Usuario;
use App\Resultados\ResultadoCalificacion;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ServicioCalificacion
{
    public function __construct(
        private readonly ServicioDesbloqueo $servicioDesbloqueo,
        private readonly ServicioProgreso $servicioProgreso,
    ) {}

    public function calificar(
        Usuario $usuario,
        Modulo $modulo,
        string $actividadUuid,
        mixed $respuesta,
    ): ResultadoCalificacion {
        return DB::transaction(function () use ($usuario, $modulo, $actividadUuid, $respuesta): ResultadoCalificacion {
            $usuarioActual = Usuario::query()->lockForUpdate()->findOrFail($usuario->getKey());
            $moduloActual = Modulo::query()->sharedLock()->findOrFail($modulo->getKey());

            if (! $this->servicioDesbloqueo->moduloEstaDesbloqueado($usuarioActual, $moduloActual)) {
                throw new AuthorizationException('El módulo no está disponible para este estudiante.');
            }

            $actividad = $this->obtenerActividad($moduloActual, $actividadUuid);
            $tipoActividad = TipoActividad::tryFrom($actividad['tipo'] ?? '');

            if ($tipoActividad === null) {
                throw new DomainException('La actividad tiene un tipo no admitido.');
            }

            $evaluacion = $this->evaluar($tipoActividad, $actividad, $respuesta);
            $numeroIntento = ((int) IntentoActividad::query()
                ->where('usuario_id', $usuarioActual->getKey())
                ->where('modulo_id', $moduloActual->getKey())
                ->where('actividad_uuid', $actividadUuid)
                ->max('numero_intento')) + 1;

            IntentoActividad::query()->create([
                'usuario_id' => $usuarioActual->getKey(),
                'modulo_id' => $moduloActual->getKey(),
                'actividad_uuid' => $actividadUuid,
                'tipo_actividad' => $tipoActividad->value,
                'numero_intento' => $numeroIntento,
                'respuesta' => $evaluacion['respuesta'],
                'correcta' => $evaluacion['correcta'],
                'respondido_en' => now(),
            ]);

            if ($this->servicioProgreso->puedeFinalizarModulo($usuarioActual, $moduloActual)) {
                $this->servicioProgreso->finalizarModulo($usuarioActual, $moduloActual);
            }

            return new ResultadoCalificacion(
                actividadUuid: $actividadUuid,
                correcta: $evaluacion['correcta'],
                actividadCompletada: $evaluacion['completada'],
                numeroIntento: $numeroIntento,
                moduloCompletado: $this->servicioProgreso->moduloEstaCompletado($usuarioActual, $moduloActual),
            );
        });
    }

    /** @return array<string, mixed> */
    private function obtenerActividad(Modulo $modulo, string $actividadUuid): array
    {
        $coincidencias = array_values(array_filter(
            $modulo->actividades,
            fn (mixed $actividad): bool => is_array($actividad)
                && ($actividad['uuid'] ?? null) === $actividadUuid,
        ));

        if (count($coincidencias) !== 1) {
            throw ValidationException::withMessages([
                'actividad' => 'La actividad solicitada no existe o no es válida.',
            ]);
        }

        return $coincidencias[0];
    }

    /**
     * @param  array<string, mixed>  $actividad
     * @return array{correcta: bool|null, completada: bool, respuesta: array<string, mixed>}
     */
    private function evaluar(TipoActividad $tipoActividad, array $actividad, mixed $respuesta): array
    {
        return match ($tipoActividad) {
            TipoActividad::FalsoVerdadero => $this->evaluarFalsoVerdadero($actividad, $respuesta),
            TipoActividad::OpcionMultiple => $this->evaluarOpcionMultiple($actividad, $respuesta),
            TipoActividad::RespuestaDirecta => $this->evaluarRespuestaDirecta($respuesta),
            TipoActividad::Ordenacion => $this->evaluarOrdenacion($actividad, $respuesta),
            TipoActividad::Clasificacion => $this->evaluarClasificacion($actividad, $respuesta),
        };
    }

    /** @return array{correcta: bool, completada: bool, respuesta: array{valor: bool}} */
    private function evaluarFalsoVerdadero(array $actividad, mixed $respuesta): array
    {
        if (! is_bool($actividad['respuesta_correcta'] ?? null)) {
            throw new DomainException('La actividad de falso o verdadero está mal configurada.');
        }

        if (! is_bool($respuesta)) {
            $this->respuestaInvalida('La respuesta debe ser verdadera o falsa.');
        }

        $correcta = $respuesta === $actividad['respuesta_correcta'];

        return [
            'correcta' => $correcta,
            'completada' => $correcta,
            'respuesta' => ['valor' => $respuesta],
        ];
    }

    /** @return array{correcta: bool, completada: bool, respuesta: array{opcion_uuid: string}} */
    private function evaluarOpcionMultiple(array $actividad, mixed $respuesta): array
    {
        $opciones = $actividad['opciones'] ?? null;
        $opcionCorrectaUuid = $actividad['opcion_correcta_uuid'] ?? null;

        if (! is_array($opciones) || count($opciones) < 2 || ! is_string($opcionCorrectaUuid)) {
            throw new DomainException('La actividad de opción múltiple está mal configurada.');
        }

        $opcionesUuid = array_map(
            fn (mixed $opcion): mixed => is_array($opcion) ? ($opcion['uuid'] ?? null) : null,
            $opciones,
        );

        if (count(array_unique($opcionesUuid, SORT_REGULAR)) !== count($opcionesUuid)
            || array_any($opcionesUuid, fn (mixed $uuid): bool => ! is_string($uuid) || $uuid === '')
            || ! in_array($opcionCorrectaUuid, $opcionesUuid, true)) {
            throw new DomainException('La actividad de opción múltiple está mal configurada.');
        }

        if (! is_string($respuesta) || ! in_array($respuesta, $opcionesUuid, true)) {
            $this->respuestaInvalida('Selecciona una opción válida.');
        }

        $correcta = $respuesta === $opcionCorrectaUuid;

        return [
            'correcta' => $correcta,
            'completada' => $correcta,
            'respuesta' => ['opcion_uuid' => $respuesta],
        ];
    }

    /** @return array{correcta: null, completada: true, respuesta: array{texto: string}} */
    private function evaluarRespuestaDirecta(mixed $respuesta): array
    {
        if (! is_string($respuesta)) {
            $this->respuestaInvalida('Escribe una respuesta válida.');
        }

        $texto = trim($respuesta);

        if ($texto === '' || mb_strlen($texto) > 10000) {
            $this->respuestaInvalida('Escribe una respuesta válida de hasta 10000 caracteres.');
        }

        return [
            'correcta' => null,
            'completada' => true,
            'respuesta' => ['texto' => $texto],
        ];
    }

    /** @return array{correcta: bool, completada: bool, respuesta: array{elementos: array<int, string>}} */
    private function evaluarOrdenacion(array $actividad, mixed $respuesta): array
    {
        $elementos = $actividad['elementos'] ?? null;

        if (! is_array($elementos) || count($elementos) < 2) {
            throw new DomainException('La actividad de ordenación está mal configurada.');
        }

        $elementosConfigurados = [];

        foreach ($elementos as $elemento) {
            $uuid = is_array($elemento) ? ($elemento['uuid'] ?? null) : null;
            $posicion = is_array($elemento) ? ($elemento['posicion'] ?? null) : null;

            if (! is_string($uuid) || $uuid === '' || ! is_numeric($posicion)) {
                throw new DomainException('La actividad de ordenación está mal configurada.');
            }

            $elementosConfigurados[] = ['uuid' => $uuid, 'posicion' => (int) $posicion];
        }

        $uuids = array_column($elementosConfigurados, 'uuid');
        $posiciones = array_column($elementosConfigurados, 'posicion');

        if (count(array_unique($uuids)) !== count($uuids)
            || count(array_unique($posiciones)) !== count($posiciones)) {
            throw new DomainException('La actividad de ordenación está mal configurada.');
        }

        if (! is_array($respuesta) || ! array_is_list($respuesta)
            || count($respuesta) !== count($uuids)
            || count(array_unique($respuesta, SORT_REGULAR)) !== count($respuesta)
            || array_any($respuesta, fn (mixed $uuid): bool => ! is_string($uuid))
            || array_diff($respuesta, $uuids) !== []
            || array_diff($uuids, $respuesta) !== []) {
            $this->respuestaInvalida('Ordena todos los elementos de la actividad.');
        }

        usort(
            $elementosConfigurados,
            fn (array $primero, array $segundo): int => $primero['posicion'] <=> $segundo['posicion'],
        );

        $ordenCorrecto = array_column($elementosConfigurados, 'uuid');
        $correcta = $respuesta === $ordenCorrecto;

        return [
            'correcta' => $correcta,
            'completada' => $correcta,
            'respuesta' => ['elementos' => $respuesta],
        ];
    }

    /** @return array{correcta: bool, completada: bool, respuesta: array{categorias: array<string, array<int, string>>}} */
    private function evaluarClasificacion(array $actividad, mixed $respuesta): array
    {
        $categorias = $actividad['categorias'] ?? null;

        if (! is_array($categorias) || count($categorias) < 2) {
            throw new DomainException('La actividad de clasificación está mal configurada.');
        }

        $clasificacionCorrecta = [];
        $elementosEsperados = [];

        foreach ($categorias as $categoria) {
            $categoriaUuid = is_array($categoria) ? ($categoria['uuid'] ?? null) : null;
            $elementos = is_array($categoria) ? ($categoria['elementos'] ?? null) : null;

            if (! is_string($categoriaUuid) || $categoriaUuid === '' || ! is_array($elementos)
                || array_key_exists($categoriaUuid, $clasificacionCorrecta)) {
                throw new DomainException('La actividad de clasificación está mal configurada.');
            }

            $elementosCategoria = [];

            foreach ($elementos as $elemento) {
                $elementoUuid = is_array($elemento) ? ($elemento['uuid'] ?? null) : null;

                if (! is_string($elementoUuid) || $elementoUuid === ''
                    || in_array($elementoUuid, $elementosEsperados, true)) {
                    throw new DomainException('La actividad de clasificación está mal configurada.');
                }

                $elementosCategoria[] = $elementoUuid;
                $elementosEsperados[] = $elementoUuid;
            }

            sort($elementosCategoria);
            $clasificacionCorrecta[$categoriaUuid] = $elementosCategoria;
        }

        if (! is_array($respuesta) || array_is_list($respuesta)) {
            $this->respuestaInvalida('Clasifica todos los elementos en categorías válidas.');
        }

        $clasificacionRespondida = [];
        $elementosRespondidos = [];

        foreach ($respuesta as $categoriaUuid => $elementos) {
            if (! is_string($categoriaUuid) || ! array_key_exists($categoriaUuid, $clasificacionCorrecta)
                || ! is_array($elementos) || ! array_is_list($elementos)
                || array_any($elementos, fn (mixed $uuid): bool => ! is_string($uuid))) {
                $this->respuestaInvalida('Clasifica todos los elementos en categorías válidas.');
            }

            $elementosRespondidos = [...$elementosRespondidos, ...$elementos];
            sort($elementos);
            $clasificacionRespondida[$categoriaUuid] = $elementos;
        }

        sort($elementosEsperados);
        $elementosRespondidosOrdenados = $elementosRespondidos;
        sort($elementosRespondidosOrdenados);
        ksort($clasificacionCorrecta);
        ksort($clasificacionRespondida);

        if (array_keys($clasificacionRespondida) !== array_keys($clasificacionCorrecta)
            || count(array_unique($elementosRespondidos)) !== count($elementosRespondidos)
            || $elementosRespondidosOrdenados !== $elementosEsperados) {
            $this->respuestaInvalida('Clasifica todos los elementos en categorías válidas.');
        }

        $correcta = $clasificacionRespondida === $clasificacionCorrecta;

        return [
            'correcta' => $correcta,
            'completada' => $correcta,
            'respuesta' => ['categorias' => $clasificacionRespondida],
        ];
    }

    private function respuestaInvalida(string $mensaje): never
    {
        throw ValidationException::withMessages(['respuesta' => $mensaje]);
    }
}
