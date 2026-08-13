<?php

namespace Database\Factories;

use App\Models\Modulo;
use App\Models\ProgresoModuloUsuario;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ProgresoModuloUsuario> */
class ProgresoModuloUsuarioFactory extends Factory
{
    protected $model = ProgresoModuloUsuario::class;

    public function definition(): array
    {
        return [
            'usuario_id' => Usuario::factory(),
            'modulo_id' => Modulo::factory(),
            'completado_en' => now(),
        ];
    }
}
