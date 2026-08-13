<?php

namespace App\Http\Controllers\Autenticacion;

use App\Enums\RolUsuario;
use App\Http\Controllers\Controller;
use App\Http\Requests\Autenticacion\SolicitudInicioSesion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SesionEstudianteController extends Controller
{
    private const MAXIMOS_INTENTOS = 5;

    private const SEGUNDOS_BLOQUEO = 60;

    public function autenticar(SolicitudInicioSesion $solicitud): RedirectResponse
    {
        $datos = $solicitud->validated();
        $claveLimite = $this->claveLimite($datos['correo_electronico'], $solicitud->ip());

        if (RateLimiter::tooManyAttempts($claveLimite, self::MAXIMOS_INTENTOS)) {
            $segundos = RateLimiter::availableIn($claveLimite);

            throw ValidationException::withMessages([
                'correo_electronico' => "Demasiados intentos. Inténtalo de nuevo en {$segundos} segundos.",
            ]);
        }

        $autenticado = Auth::guard('web')->attempt([
            'correo_electronico' => $datos['correo_electronico'],
            'password' => $datos['contrasena'],
            'rol' => RolUsuario::Estudiante->value,
            'activo' => true,
        ]);

        if (! $autenticado) {
            RateLimiter::hit($claveLimite, self::SEGUNDOS_BLOQUEO);

            throw ValidationException::withMessages([
                'correo_electronico' => 'Las credenciales proporcionadas no son válidas.',
            ]);
        }

        RateLimiter::clear($claveLimite);
        $solicitud->session()->regenerate();

        return redirect()->intended(route('portal.inicio', absolute: false));
    }

    public function cerrar(Request $solicitud): RedirectResponse
    {
        Auth::guard('web')->logout();
        $solicitud->session()->invalidate();
        $solicitud->session()->regenerateToken();

        return redirect()->route('estudiante.login');
    }

    private function claveLimite(string $correoElectronico, ?string $direccionIp): string
    {
        $identificador = Str::lower(trim($correoElectronico)).'|'.($direccionIp ?? 'sin-ip');

        return 'inicio-sesion-estudiante:'.hash('sha256', $identificador);
    }
}
