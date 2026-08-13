<?php

namespace App\Resultados;

use JsonSerializable;

final readonly class ResultadoCalificacion implements JsonSerializable
{
    public function __construct(
        public string $actividadUuid,
        public ?bool $correcta,
        public bool $actividadCompletada,
        public int $numeroIntento,
        public bool $moduloCompletado,
    ) {}

    /**
     * @return array{
     *     actividad_uuid: string,
     *     correcta: bool|null,
     *     actividad_completada: bool,
     *     numero_intento: int,
     *     modulo_completado: bool
     * }
     */
    public function toArray(): array
    {
        return [
            'actividad_uuid' => $this->actividadUuid,
            'correcta' => $this->correcta,
            'actividad_completada' => $this->actividadCompletada,
            'numero_intento' => $this->numeroIntento,
            'modulo_completado' => $this->moduloCompletado,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
