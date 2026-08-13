<?php

namespace Database\Factories;

use App\Models\Colegio;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Colegio> */
class ColegioFactory extends Factory
{
    protected $model = Colegio::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->company(),
            'codigo' => fake()->unique()->bothify('COL-###-???'),
            'activo' => true,
        ];
    }
}
