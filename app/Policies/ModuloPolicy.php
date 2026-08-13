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

    public function viewAny(Usuario $usuario): bool
    {
        return $this->esSuperadministradorActivo($usuario);
    }

    public function view(Usuario $usuario, Modulo $modulo): bool
    {
        if ($this->esSuperadministradorActivo($usuario)) {
            return true;
        }

        return $this->servicioDesbloqueo->moduloEstaDesbloqueado($usuario, $modulo);
    }

    public function create(Usuario $usuario): bool
    {
        return $this->esSuperadministradorActivo($usuario);
    }

    public function update(Usuario $usuario, Modulo $modulo): bool
    {
        return $this->esSuperadministradorActivo($usuario);
    }

    public function delete(Usuario $usuario, Modulo $modulo): bool
    {
        return false;
    }

    private function esSuperadministradorActivo(Usuario $usuario): bool
    {
        return $usuario->activo && $usuario->rol === RolUsuario::Superadministrador;
    }
}
