<?php

namespace App\Services;

use App\Enums\TipoActividad;
use App\Models\IntentoActividad;
use App\Models\Modulo;
use App\Models\Usuario;
use DomainException;

class ServicioEstadoModulo
{
    /** @return array<string, true> */
    public function obtenerActividadesCompletadas(Usuario $usuario, Modulo $modulo): array
    {
        $tiposPorUuid = $this->obtenerTiposPorUuid($modulo);

        if ($tiposPorUuid === []) {
            return [];
        }

        return IntentoActividad::query()
            ->where('usuario_id', $usuario->getKey())
            ->where('modulo_id', $modulo->getKey())
            ->whereIn('actividad_uuid', array_keys($tiposPorUuid))
            ->get(['actividad_uuid', 'tipo_actividad', 'correcta'])
            ->filter(function (IntentoActividad $intento) use ($tiposPorUuid): bool {
                $tipo = $tiposPorUuid[$intento->actividad_uuid] ?? null;

                return $tipo !== null
                    && $intento->tipo_actividad === $tipo->value
                    && ($tipo === TipoActividad::RespuestaDirecta || $intento->correcta === true);
            })
            ->mapWithKeys(fn (IntentoActividad $intento): array => [$intento->actividad_uuid => true])
            ->all();
    }

    public function obtenerIndicePrimeraPendiente(Usuario $usuario, Modulo $modulo): int
    {
        $completadas = $this->obtenerActividadesCompletadas($usuario, $modulo);

        foreach ($modulo->actividades ?? [] as $indice => $actividad) {
            if (! is_array($actividad)) {
                throw new DomainException('El módulo contiene una actividad mal configurada.');
            }

            $uuid = $actividad['uuid'] ?? null;

            if (! is_string($uuid) || ! isset($completadas[$uuid])) {
                return $indice;
            }
        }

        return count($modulo->actividades ?? []);
    }

    /**
     * Devuelve exclusivamente los datos que el navegador necesita para presentar la actividad.
     *
     * @return array<string, mixed>|null
     */
    public function presentarActividad(Usuario $usuario, Modulo $modulo, int $indice): ?array
    {
        $actividad = ($modulo->actividades ?? [])[$indice] ?? null;

        if ($actividad === null) {
            return null;
        }

        if (! is_array($actividad)) {
            throw new DomainException('El módulo contiene una actividad mal configurada.');
        }

        $uuid = $actividad['uuid'] ?? null;
        $tipo = TipoActividad::tryFrom($actividad['tipo'] ?? '');

        if (! is_string($uuid) || $uuid === '' || $tipo === null) {
            throw new DomainException('El módulo contiene una actividad mal configurada.');
        }

        $base = [
            'uuid' => $uuid,
            'tipo' => $tipo->value,
            'completada' => isset($this->obtenerActividadesCompletadas($usuario, $modulo)[$uuid]),
        ];

        return match ($tipo) {
            TipoActividad::FalsoVerdadero,
            TipoActividad::RespuestaDirecta => [
                ...$base,
                'pregunta' => $this->textoRequerido($actividad, 'pregunta'),
            ],
            TipoActividad::OpcionMultiple => [
                ...$base,
                'pregunta' => $this->textoRequerido($actividad, 'pregunta'),
                'opciones' => $this->presentarOpciones($actividad),
            ],
            TipoActividad::Ordenacion => [
                ...$base,
                'instruccion' => $this->textoRequerido($actividad, 'instruccion'),
                'elementos' => $this->presentarElementosOrdenacion($usuario, $modulo, $actividad),
            ],
            TipoActividad::Clasificacion => [
                ...$base,
                'instruccion' => $this->textoRequerido($actividad, 'instruccion'),
                'categorias' => $this->presentarCategorias($actividad),
                'elementos' => $this->presentarElementosClasificacion($usuario, $modulo, $actividad),
            ],
        };
    }

    /** @return array<string, TipoActividad> */
    private function obtenerTiposPorUuid(Modulo $modulo): array
    {
        $tipos = [];

        foreach ($modulo->actividades ?? [] as $actividad) {
            if (! is_array($actividad)) {
                throw new DomainException('El módulo contiene una actividad mal configurada.');
            }

            $uuid = $actividad['uuid'] ?? null;
            $tipo = TipoActividad::tryFrom($actividad['tipo'] ?? '');

            if (! is_string($uuid) || $uuid === '' || $tipo === null || isset($tipos[$uuid])) {
                throw new DomainException('El módulo contiene una actividad mal configurada.');
            }

            $tipos[$uuid] = $tipo;
        }

        return $tipos;
    }

    /** @return array<int, array{uuid: string, texto: string}> */
    private function presentarOpciones(array $actividad): array
    {
        return $this->extraerElementos($actividad['opciones'] ?? null);
    }

    /** @return array<int, array{uuid: string, texto: string}> */
    private function presentarElementosOrdenacion(Usuario $usuario, Modulo $modulo, array $actividad): array
    {
        $configurados = $actividad['elementos'] ?? null;
        $elementos = $this->extraerElementos($configurados);

        if (! is_array($configurados) || count($elementos) < 2) {
            throw new DomainException('La actividad de ordenación está mal configurada.');
        }

        $correctos = $configurados;
        usort($correctos, fn (array $primero, array $segundo): int => ($primero['posicion'] ?? 0) <=> ($segundo['posicion'] ?? 0));
        $ordenCorrecto = array_column($correctos, 'uuid');
        $elementos = $this->mezclarEstablemente($elementos, $usuario, $modulo, (string) $actividad['uuid']);

        if (array_column($elementos, 'uuid') === $ordenCorrecto) {
            $primero = array_shift($elementos);
            $elementos[] = $primero;
        }

        return array_values($elementos);
    }

    /** @return array<int, array{uuid: string, nombre: string}> */
    private function presentarCategorias(array $actividad): array
    {
        $categorias = $actividad['categorias'] ?? null;

        if (! is_array($categorias) || count($categorias) < 2) {
            throw new DomainException('La actividad de clasificación está mal configurada.');
        }

        return array_map(function (mixed $categoria): array {
            if (! is_array($categoria)) {
                throw new DomainException('La actividad de clasificación está mal configurada.');
            }

            return [
                'uuid' => $this->textoRequerido($categoria, 'uuid'),
                'nombre' => $this->textoRequerido($categoria, 'nombre'),
            ];
        }, array_values($categorias));
    }

    /** @return array<int, array{uuid: string, texto: string}> */
    private function presentarElementosClasificacion(Usuario $usuario, Modulo $modulo, array $actividad): array
    {
        $elementos = [];

        foreach ($actividad['categorias'] ?? [] as $categoria) {
            if (! is_array($categoria)) {
                throw new DomainException('La actividad de clasificación está mal configurada.');
            }

            $elementos = [...$elementos, ...$this->extraerElementos($categoria['elementos'] ?? null)];
        }

        if ($elementos === []) {
            throw new DomainException('La actividad de clasificación está mal configurada.');
        }

        return $this->mezclarEstablemente($elementos, $usuario, $modulo, (string) $actividad['uuid']);
    }

    /** @return array<int, array{uuid: string, texto: string}> */
    private function extraerElementos(mixed $elementos): array
    {
        if (! is_array($elementos)) {
            throw new DomainException('La actividad contiene elementos mal configurados.');
        }

        return array_map(function (mixed $elemento): array {
            if (! is_array($elemento)) {
                throw new DomainException('La actividad contiene elementos mal configurados.');
            }

            return [
                'uuid' => $this->textoRequerido($elemento, 'uuid'),
                'texto' => $this->textoRequerido($elemento, 'texto'),
            ];
        }, array_values($elementos));
    }

    /**
     * @param  array<int, array{uuid: string, texto: string}>  $elementos
     * @return array<int, array{uuid: string, texto: string}>
     */
    private function mezclarEstablemente(array $elementos, Usuario $usuario, Modulo $modulo, string $actividadUuid): array
    {
        usort($elementos, function (array $primero, array $segundo) use ($usuario, $modulo, $actividadUuid): int {
            $contexto = $usuario->getKey().'|'.$modulo->getKey().'|'.$actividadUuid.'|';
            $primeraClave = hash_hmac('sha256', $contexto.$primero['uuid'], (string) config('app.key'));
            $segundaClave = hash_hmac('sha256', $contexto.$segundo['uuid'], (string) config('app.key'));

            return $primeraClave <=> $segundaClave;
        });

        return array_values($elementos);
    }

    private function textoRequerido(array $datos, string $clave): string
    {
        $valor = $datos[$clave] ?? null;

        if (! is_string($valor) || trim($valor) === '') {
            throw new DomainException('La actividad está mal configurada.');
        }

        return $valor;
    }
}
