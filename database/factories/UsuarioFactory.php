<?php

namespace Database\Factories;

use App\Enums\RolUsuario;
use App\Models\Colegio;
use App\Models\GradoAcademico;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** @extends Factory<Usuario> */
class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->name(),
            'correo_electronico' => fake()->unique()->safeEmail(),
            'contrasena' => Hash::make(Str::random(32)),
            'rol' => RolUsuario::Estudiante,
            'colegio_id' => Colegio::factory(),
            'grado_academico_id' => GradoAcademico::factory(),
            'activo' => true,
            'token_recordatorio' => null,
        ];
    }

    public function superadministrador(): static
    {
        return $this->state(fn (): array => [
            'rol' => RolUsuario::Superadministrador,
            'colegio_id' => null,
            'grado_academico_id' => null,
        ]);
    }

    public function inactivo(): static
    {
        return $this->state(fn (): array => [
            'activo' => false,
        ]);
    }
}
