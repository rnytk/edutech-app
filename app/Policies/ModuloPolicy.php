<?php

namespace App\Policies;

use App\Enums\RolUsuario;
use App\Models\Modulo;
use App\Models\Usuario;
use App\Services\ServicioDesbloqueo;

class ModuloPolicy
{
    public function __construct(
        private readonly ServicioDesbloqueo $servicioDesbloqueo,
    ) {}

    public function view(Usuario $usuario, Modulo $modulo): bool
    {
        if ($usuario->activo && $usuario->rol === RolUsuario::Superadministrador) {
            return true;
        }

        return $this->servicioDesbloqueo->moduloEstaDesbloqueado($usuario, $modulo);
    }
}
