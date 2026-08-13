<?php

namespace Database\Factories;

use App\Models\Modulo;
use App\Models\Nivel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Modulo> */
class ModuloFactory extends Factory
{
    protected $model = Modulo::class;

    public function definition(): array
    {
        return [
            'nivel_id' => Nivel::factory(),
            'titulo' => fake()->sentence(3),
            'descripcion' => fake()->paragraph(),
            'ruta_imagen' => null,
            'orden' => fake()->numberBetween(1, 10),
            'bloques_contenido' => [
                [
                    'tipo' => 'tarjeta',
                    'uuid' => (string) Str::uuid(),
                    'titulo' => fake()->sentence(3),
                    'contenido' => fake()->paragraph(),
                    'ruta_imagen' => null,
                ],
            ],
            'actividades' => [],
            'mensaje_cierre' => fake()->sentence(),
            'publicado' => false,
        ];
    }
}
