<?php

namespace App\Policies;

use App\Enums\RolUsuario;
use App\Models\Colegio;
use App\Models\Usuario;

class ColegioPolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return $this->administra($usuario);
    }

    public function view(Usuario $usuario, Colegio $colegio): bool
    {
        return $this->administra($usuario);
    }

    public function create(Usuario $usuario): bool
    {
        return $this->administra($usuario);
    }

    public function update(Usuario $usuario, Colegio $colegio): bool
    {
        return $this->administra($usuario);
    }

    public function delete(Usuario $usuario, Colegio $colegio): bool
    {
        return false;
    }

    private function administra(Usuario $usuario): bool
    {
        return $usuario->activo && $usuario->rol === RolUsuario::Superadministrador;
    }
}
