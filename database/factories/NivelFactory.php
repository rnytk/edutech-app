<?php

namespace Database\Factories;

use App\Models\Curso;
use App\Models\Nivel;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Nivel> */
class NivelFactory extends Factory
{
    protected $model = Nivel::class;

    public function definition(): array
    {
        return [
            'curso_id' => Curso::factory(),
            'titulo' => 'Nivel '.fake()->numberBetween(1, 10),
            'descripcion' => fake()->paragraph(),
            'ruta_imagen' => null,
            'orden' => fake()->numberBetween(1, 10),
            'publicado' => false,
        ];
    }
}
