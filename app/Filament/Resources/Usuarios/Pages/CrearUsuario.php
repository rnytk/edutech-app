<?php

namespace App\Filament\Resources\Usuarios\Pages;

use App\Enums\RolUsuario;
use App\Filament\Resources\Usuarios\UsuarioResource;
use Filament\Resources\Pages\CreateRecord;

class CrearUsuario extends CreateRecord
{
    protected static string $resource = UsuarioResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if ($data['rol'] === RolUsuario::Superadministrador->value) {
            $data['colegio_id'] = null;
            $data['grado_academico_id'] = null;
        }

        return $data;
    }
}
