<?php

namespace Database\Factories;

use App\Models\AsignacionCurso;
use App\Models\Colegio;
use App\Models\Curso;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AsignacionCurso> */
class AsignacionCursoFactory extends Factory
{
    protected $model = AsignacionCurso::class;

    public function definition(): array
    {
        return [
            'curso_id' => Curso::factory(),
            'colegio_id' => Colegio::factory(),
            'grado_academico_id' => null,
            'activo' => true,
            'inicia_en' => now()->subDay(),
            'finaliza_en' => now()->addYear(),
        ];
    }
}
