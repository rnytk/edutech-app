<?php

namespace Tests\Unit\Filament;

use App\Filament\Soporte\TransformadorContenidoModulo;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TransformadorContenidoModuloTest extends TestCase
{
    public function test_rechaza_opcion_multiple_sin_dos_opciones(): void
    {
        $this->expectException(ValidationException::class);

        (new TransformadorContenidoModulo)->paraPersistenciaActividades([[
            'type' => 'opcion_multiple',
            'data' => [
                'uuid' => (string) Str::uuid(),
                'pregunta' => 'Pregunta',
                'opciones' => [['uuid' => (string) Str::uuid(), 'texto' => 'Una opción']],
                'opcion_correcta_uuid' => (string) Str::uuid(),
            ],
        ]]);
    }

    public function test_rechaza_ordenacion_sin_dos_elementos(): void
    {
        $this->expectException(ValidationException::class);

        (new TransformadorContenidoModulo)->paraPersistenciaActividades([[
            'type' => 'ordenacion',
            'data' => [
                'uuid' => (string) Str::uuid(),
                'instruccion' => 'Ordena',
                'elementos' => [['uuid' => (string) Str::uuid(), 'texto' => 'Uno', 'posicion' => 1]],
            ],
        ]]);
    }

    public function test_rechaza_clasificacion_sin_dos_categorias(): void
    {
        $this->expectException(ValidationException::class);

        (new TransformadorContenidoModulo)->paraPersistenciaActividades([[
            'type' => 'clasificacion',
            'data' => [
                'uuid' => (string) Str::uuid(),
                'instruccion' => 'Clasifica',
                'categorias' => [[
                    'uuid' => (string) Str::uuid(),
                    'nombre' => 'Una',
                    'elementos' => [],
                ]],
            ],
        ]]);
    }

    public function test_genera_uuid_faltantes_y_conserva_los_existentes(): void
    {
        $uuid = (string) Str::uuid();
        $transformador = new TransformadorContenidoModulo;

        $actividades = $transformador->paraPersistenciaActividades([
            ['type' => 'respuesta_directa', 'data' => ['uuid' => $uuid, 'pregunta' => 'Primera']],
            ['type' => 'respuesta_directa', 'data' => ['pregunta' => 'Segunda']],
        ]);

        $this->assertSame($uuid, $actividades[0]['uuid']);
        $this->assertTrue(Str::isUuid($actividades[1]['uuid']));
    }
}
