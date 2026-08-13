<?php

namespace App\Http\Requests\Autenticacion;

use Illuminate\Foundation\Http\FormRequest;

class SolicitudInicioSesion extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'correo_electronico' => ['required', 'string', 'email:rfc', 'max:255'],
            'contrasena' => ['required', 'string', 'max:255'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'correo_electronico.required' => 'Ingresa tu correo electrónico.',
            'correo_electronico.email' => 'Ingresa un correo electrónico válido.',
            'correo_electronico.max' => 'El correo electrónico es demasiado largo.',
            'contrasena.required' => 'Ingresa tu contraseña.',
            'contrasena.max' => 'La contraseña es demasiado larga.',
        ];
    }
}
