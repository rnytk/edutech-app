<?php

namespace Database\Factories;

use App\Models\IntentoActividad;
use App\Models\Modulo;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<IntentoActividad> */
class IntentoActividadFactory extends Factory
{
    protected $model = IntentoActividad::class;

    public function definition(): array
    {
        return [
            'usuario_id' => Usuario::factory(),
            'modulo_id' => Modulo::factory(),
            'actividad_uuid' => (string) Str::uuid(),
            'tipo_actividad' => fake()->randomElement([
                'falso_verdadero',
                'opcion_multiple',
                'respuesta_directa',
                'ordenacion',
                'clasificacion',
            ]),
            'numero_intento' => fake()->numberBetween(1, 5),
            'respuesta' => ['valor' => fake()->word()],
            'correcta' => fake()->randomElement([true, false, null]),
            'respondido_en' => now(),
        ];
    }
}
