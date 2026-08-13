<?php

namespace App\Http\Middleware;

use App\Enums\RolUsuario;
use App\Models\Usuario;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AsegurarEstudianteActivo
{
    public function handle(Request $solicitud, Closure $siguiente): Response|RedirectResponse
    {
        $usuario = $solicitud->user();

        if (! $usuario instanceof Usuario || $usuario->rol !== RolUsuario::Estudiante) {
            abort(Response::HTTP_FORBIDDEN);
        }

        if (! $usuario->activo) {
            Auth::guard('web')->logout();
            $solicitud->session()->invalidate();
            $solicitud->session()->regenerateToken();

            return redirect()->route('estudiante.login');
        }

        return $siguiente($solicitud);
    }
}
