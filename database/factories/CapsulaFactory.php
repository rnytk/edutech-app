<?php

namespace Database\Factories;

use App\Models\Capsula;
use App\Models\Modulo;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Capsula> */
class CapsulaFactory extends Factory
{
    protected $model = Capsula::class;

    public function definition(): array
    {
        return [
            'modulo_id' => Modulo::factory(),
            'titulo' => fake()->optional()->sentence(3),
            'contenido' => fake()->paragraph(),
            'ruta_imagen' => null,
            'orden' => fake()->numberBetween(1, 10),
            'activo' => true,
        ];
    }
}
