<?php

namespace App\Filament\Pages\Autenticacion;

use Filament\Auth\Pages\Login;
use SensitiveParameter;

class IniciarSesion extends Login
{
    /** @return array<string, mixed> */
    protected function getCredentialsFromFormData(#[SensitiveParameter] array $data): array
    {
        return [
            'correo_electronico' => $data['email'],
            'password' => $data['password'],
        ];
    }
}
