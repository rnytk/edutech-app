<?php

namespace Database\Factories;

use App\Models\GradoAcademico;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<GradoAcademico> */
class GradoAcademicoFactory extends Factory
{
    protected $model = GradoAcademico::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->randomElement(['Primero basico', 'Segundo basico', 'Tercero basico', 'Bachillerato']),
            'codigo' => fake()->unique()->bothify('GRA-###-???'),
            'orden' => fake()->numberBetween(1, 12),
            'activo' => true,
        ];
    }
}
