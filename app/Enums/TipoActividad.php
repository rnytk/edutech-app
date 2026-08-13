<?php

namespace App\Enums;

enum TipoActividad: string
{
    case FalsoVerdadero = 'falso_verdadero';
    case OpcionMultiple = 'opcion_multiple';
    case RespuestaDirecta = 'respuesta_directa';
    case Ordenacion = 'ordenacion';
    case Clasificacion = 'clasificacion';
}
