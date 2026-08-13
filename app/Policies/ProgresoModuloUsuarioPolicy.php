<?php

namespace App\Policies;

use App\Enums\RolUsuario;
use App\Models\ProgresoModuloUsuario;
use App\Models\Usuario;

class ProgresoModuloUsuarioPolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->activo && $usuario->rol === RolUsuario::Superadministrador;
    }

    public function view(Usuario $usuario, ProgresoModuloUsuario $progresoModuloUsuario): bool
    {
        return $usuario->activo
            && ($usuario->rol === RolUsuario::Superadministrador
                || $progresoModuloUsuario->usuario_id === $usuario->getKey());
    }

    public function update(Usuario $usuario, ProgresoModuloUsuario $progresoModuloUsuario): bool
    {
        return false;
    }

    public function delete(Usuario $usuario, ProgresoModuloUsuario $progresoModuloUsuario): bool
    {
        return false;
    }
}
