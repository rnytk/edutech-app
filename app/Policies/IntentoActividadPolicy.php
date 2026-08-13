<?php

namespace App\Policies;

use App\Enums\RolUsuario;
use App\Models\IntentoActividad;
use App\Models\Usuario;

class IntentoActividadPolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->activo && $usuario->rol === RolUsuario::Superadministrador;
    }

    public function view(Usuario $usuario, IntentoActividad $intentoActividad): bool
    {
        return $usuario->activo
            && ($usuario->rol === RolUsuario::Superadministrador
                || $intentoActividad->usuario_id === $usuario->getKey());
    }

    public function update(Usuario $usuario, IntentoActividad $intentoActividad): bool
    {
        return false;
    }

    public function delete(Usuario $usuario, IntentoActividad $intentoActividad): bool
    {
        return false;
    }
}
