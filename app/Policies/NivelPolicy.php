<?php

namespace App\Policies;

use App\Enums\RolUsuario;
use App\Models\Nivel;
use App\Models\Usuario;
use App\Services\ServicioDesbloqueo;

class NivelPolicy
{
    public function __construct(
        private readonly ServicioDesbloqueo $servicioDesbloqueo,
    ) {}

    public function view(Usuario $usuario, Nivel $nivel): bool
    {
        if ($usuario->activo && $usuario->rol === RolUsuario::Superadministrador) {
            return true;
        }

        return $this->servicioDesbloqueo->nivelEstaDesbloqueado($usuario, $nivel);
    }
}
