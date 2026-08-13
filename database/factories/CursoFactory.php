<?php

namespace Database\Factories;

use App\Models\Curso;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Curso> */
class CursoFactory extends Factory
{
    protected $model = Curso::class;

    public function definition(): array
    {
        return [
            'titulo' => fake()->sentence(4),
            'descripcion' => fake()->paragraph(),
            'ruta_imagen' => null,
            'titulo_bienvenida' => null,
            'contenido_bienvenida' => null,
            'orden' => fake()->numberBetween(1, 20),
            'publicado' => false,
        ];
    }
}
