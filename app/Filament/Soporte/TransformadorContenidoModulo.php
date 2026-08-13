<?php

namespace App\Filament\Soporte;

use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TransformadorContenidoModulo
{
    /** @return array<int, array{type: string, data: array<string, mixed>}> */
    public function paraFormulario(?array $elementos): array
    {
        return array_values(array_map(static function (mixed $elemento): array {
            if (! is_array($elemento)) {
                return ['type' => '', 'data' => []];
            }

            $tipo = (string) ($elemento['tipo'] ?? '');
            unset($elemento['tipo']);

            return ['type' => $tipo, 'data' => $elemento];
        }, $elementos ?? []));
    }

    /** @return array<int, array<string, mixed>> */
    public function paraPersistenciaBloques(?array $elementos): array
    {
        $bloques = [];
        $uuidUsados = [];

        foreach (array_values($elementos ?? []) as $indice => $elementoConstructor) {
            [$tipo, $datos] = $this->separarElementoConstructor($elementoConstructor, "bloques_contenido.$indice");

            if ($tipo !== 'tarjeta') {
                $this->fallar("bloques_contenido.$indice.tipo", 'El tipo de bloque no es válido.');
            }

            $uuid = $this->uuid($datos['uuid'] ?? null, "bloques_contenido.$indice.uuid");
            $this->asegurarUuidUnico($uuid, $uuidUsados, "bloques_contenido.$indice.uuid");

            $bloques[] = [
                'tipo' => 'tarjeta',
                'uuid' => $uuid,
                'titulo' => $this->textoRequerido($datos['titulo'] ?? null, "bloques_contenido.$indice.titulo"),
                'contenido' => $this->textoRequerido($datos['contenido'] ?? null, "bloques_contenido.$indice.contenido"),
                'ruta_imagen' => $this->textoOpcional($datos['ruta_imagen'] ?? null),
            ];
        }

        return $bloques;
    }

    /** @return array<int, array<string, mixed>> */
    public function paraPersistenciaActividades(?array $elementos): array
    {
        $actividades = [];
        $uuidUsados = [];

        foreach (array_values($elementos ?? []) as $indice => $elementoConstructor) {
            [$tipo, $datos] = $this->separarElementoConstructor($elementoConstructor, "actividades.$indice");
            $uuid = $this->uuid($datos['uuid'] ?? null, "actividades.$indice.uuid");
            $this->asegurarUuidUnico($uuid, $uuidUsados, "actividades.$indice.uuid");

            $actividades[] = match ($tipo) {
                'falso_verdadero' => $this->falsoVerdadero($uuid, $datos, $indice),
                'opcion_multiple' => $this->opcionMultiple($uuid, $datos, $indice),
                'respuesta_directa' => $this->respuestaDirecta($uuid, $datos, $indice),
                'ordenacion' => $this->ordenacion($uuid, $datos, $indice),
                'clasificacion' => $this->clasificacion($uuid, $datos, $indice),
                default => $this->fallar("actividades.$indice.tipo", 'El tipo de actividad no es válido.'),
            };
        }

        return $actividades;
    }

    /** @return array{0: string, 1: array<string, mixed>} */
    private function separarElementoConstructor(mixed $elemento, string $ruta): array
    {
        if (! is_array($elemento) || ! is_array($elemento['data'] ?? null)) {
            $this->fallar($ruta, 'La estructura del elemento no es válida.');
        }

        return [(string) ($elemento['type'] ?? ''), $elemento['data']];
    }

    /** @param array<string, mixed> $datos */
    private function falsoVerdadero(string $uuid, array $datos, int $indice): array
    {
        if (! is_bool($datos['respuesta_correcta'] ?? null)) {
            $this->fallar("actividades.$indice.respuesta_correcta", 'Selecciona la respuesta correcta.');
        }

        return [
            'tipo' => 'falso_verdadero',
            'uuid' => $uuid,
            'pregunta' => $this->textoRequerido($datos['pregunta'] ?? null, "actividades.$indice.pregunta"),
            'respuesta_correcta' => $datos['respuesta_correcta'],
        ];
    }

    /** @param array<string, mixed> $datos */
    private function opcionMultiple(string $uuid, array $datos, int $indice): array
    {
        $opcionesOriginales = $datos['opciones'] ?? null;

        if (! is_array($opcionesOriginales) || count($opcionesOriginales) < 2) {
            $this->fallar("actividades.$indice.opciones", 'Agrega al menos dos opciones.');
        }

        $opciones = [];
        $uuidOpciones = [];

        foreach (array_values($opcionesOriginales) as $indiceOpcion => $opcion) {
            if (! is_array($opcion)) {
                $this->fallar("actividades.$indice.opciones.$indiceOpcion", 'La opción no es válida.');
            }

            $uuidOpcion = $this->uuid($opcion['uuid'] ?? null, "actividades.$indice.opciones.$indiceOpcion.uuid");
            $this->asegurarUuidUnico($uuidOpcion, $uuidOpciones, "actividades.$indice.opciones.$indiceOpcion.uuid");

            $opciones[] = [
                'uuid' => $uuidOpcion,
                'texto' => $this->textoRequerido($opcion['texto'] ?? null, "actividades.$indice.opciones.$indiceOpcion.texto"),
            ];
        }

        $opcionCorrectaUuid = $datos['opcion_correcta_uuid'] ?? null;

        if (! is_string($opcionCorrectaUuid) || ! in_array($opcionCorrectaUuid, $uuidOpciones, true)) {
            $this->fallar("actividades.$indice.opcion_correcta_uuid", 'Selecciona exactamente una opción correcta.');
        }

        return [
            'tipo' => 'opcion_multiple',
            'uuid' => $uuid,
            'pregunta' => $this->textoRequerido($datos['pregunta'] ?? null, "actividades.$indice.pregunta"),
            'opciones' => $opciones,
            'opcion_correcta_uuid' => $opcionCorrectaUuid,
        ];
    }

    /** @param array<string, mixed> $datos */
    private function respuestaDirecta(string $uuid, array $datos, int $indice): array
    {
        return [
            'tipo' => 'respuesta_directa',
            'uuid' => $uuid,
            'pregunta' => $this->textoRequerido($datos['pregunta'] ?? null, "actividades.$indice.pregunta"),
        ];
    }

    /** @param array<string, mixed> $datos */
    private function ordenacion(string $uuid, array $datos, int $indice): array
    {
        $elementosOriginales = $datos['elementos'] ?? null;

        if (! is_array($elementosOriginales) || count($elementosOriginales) < 2) {
            $this->fallar("actividades.$indice.elementos", 'Agrega al menos dos elementos.');
        }

        $elementos = [];
        $uuidElementos = [];
        $posiciones = [];

        foreach (array_values($elementosOriginales) as $indiceElemento => $elemento) {
            if (! is_array($elemento)) {
                $this->fallar("actividades.$indice.elementos.$indiceElemento", 'El elemento no es válido.');
            }

            $uuidElemento = $this->uuid($elemento['uuid'] ?? null, "actividades.$indice.elementos.$indiceElemento.uuid");
            $this->asegurarUuidUnico($uuidElemento, $uuidElementos, "actividades.$indice.elementos.$indiceElemento.uuid");

            $posicion = filter_var($elemento['posicion'] ?? null, FILTER_VALIDATE_INT);

            if ($posicion === false || $posicion < 1 || in_array($posicion, $posiciones, true)) {
                $this->fallar("actividades.$indice.elementos.$indiceElemento.posicion", 'Cada posición debe ser un entero positivo y no repetido.');
            }

            $posiciones[] = $posicion;
            $elementos[] = [
                'uuid' => $uuidElemento,
                'texto' => $this->textoRequerido($elemento['texto'] ?? null, "actividades.$indice.elementos.$indiceElemento.texto"),
                'posicion' => $posicion,
            ];
        }

        return [
            'tipo' => 'ordenacion',
            'uuid' => $uuid,
            'instruccion' => $this->textoRequerido($datos['instruccion'] ?? null, "actividades.$indice.instruccion"),
            'elementos' => $elementos,
        ];
    }

    /** @param array<string, mixed> $datos */
    private function clasificacion(string $uuid, array $datos, int $indice): array
    {
        $categoriasOriginales = $datos['categorias'] ?? null;

        if (! is_array($categoriasOriginales) || count($categoriasOriginales) < 2) {
            $this->fallar("actividades.$indice.categorias", 'Agrega al menos dos categorías.');
        }

        $categorias = [];
        $uuidCategorias = [];
        $uuidElementos = [];

        foreach (array_values($categoriasOriginales) as $indiceCategoria => $categoria) {
            if (! is_array($categoria) || ! is_array($categoria['elementos'] ?? null)) {
                $this->fallar("actividades.$indice.categorias.$indiceCategoria", 'La categoría no es válida.');
            }

            $uuidCategoria = $this->uuid($categoria['uuid'] ?? null, "actividades.$indice.categorias.$indiceCategoria.uuid");
            $this->asegurarUuidUnico($uuidCategoria, $uuidCategorias, "actividades.$indice.categorias.$indiceCategoria.uuid");
            $elementos = [];

            foreach (array_values($categoria['elementos']) as $indiceElemento => $elemento) {
                if (! is_array($elemento)) {
                    $this->fallar("actividades.$indice.categorias.$indiceCategoria.elementos.$indiceElemento", 'El elemento no es válido.');
                }

                $uuidElemento = $this->uuid($elemento['uuid'] ?? null, "actividades.$indice.categorias.$indiceCategoria.elementos.$indiceElemento.uuid");
                $this->asegurarUuidUnico($uuidElemento, $uuidElementos, "actividades.$indice.categorias.$indiceCategoria.elementos.$indiceElemento.uuid");

                $elementos[] = [
                    'uuid' => $uuidElemento,
                    'texto' => $this->textoRequerido($elemento['texto'] ?? null, "actividades.$indice.categorias.$indiceCategoria.elementos.$indiceElemento.texto"),
                ];
            }

            $categorias[] = [
                'uuid' => $uuidCategoria,
                'nombre' => $this->textoRequerido($categoria['nombre'] ?? null, "actividades.$indice.categorias.$indiceCategoria.nombre"),
                'elementos' => $elementos,
            ];
        }

        return [
            'tipo' => 'clasificacion',
            'uuid' => $uuid,
            'instruccion' => $this->textoRequerido($datos['instruccion'] ?? null, "actividades.$indice.instruccion"),
            'categorias' => $categorias,
        ];
    }

    private function uuid(mixed $valor, string $ruta): string
    {
        if ($valor === null || $valor === '') {
            return (string) Str::uuid();
        }

        if (! is_string($valor) || ! Str::isUuid($valor)) {
            $this->fallar($ruta, 'El UUID no es válido.');
        }

        return $valor;
    }

    /** @param array<int, string> $uuidUsados */
    private function asegurarUuidUnico(string $uuid, array &$uuidUsados, string $ruta): void
    {
        if (in_array($uuid, $uuidUsados, true)) {
            $this->fallar($ruta, 'El UUID está repetido.');
        }

        $uuidUsados[] = $uuid;
    }

    private function textoRequerido(mixed $valor, string $ruta): string
    {
        if (! is_string($valor) || trim(strip_tags($valor)) === '') {
            $this->fallar($ruta, 'Este campo es obligatorio.');
        }

        return $valor;
    }

    private function textoOpcional(mixed $valor): ?string
    {
        return is_string($valor) && $valor !== '' ? $valor : null;
    }

    private function fallar(string $ruta, string $mensaje): never
    {
        throw ValidationException::withMessages(["data.$ruta" => $mensaje]);
    }
}
