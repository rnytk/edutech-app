<?php

namespace App\Filament\Resources\Usuarios\Pages;

use App\Enums\RolUsuario;
use App\Filament\Resources\Usuarios\UsuarioResource;
use Filament\Resources\Pages\EditRecord;

class EditarUsuario extends EditRecord
{
    protected static string $resource = UsuarioResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($data['rol'] === RolUsuario::Superadministrador->value) {
            $data['colegio_id'] = null;
            $data['grado_academico_id'] = null;
        }

        return $data;
    }
}
