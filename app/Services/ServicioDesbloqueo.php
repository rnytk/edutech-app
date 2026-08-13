<?php

namespace App\Services;

use App\Models\Modulo;
use App\Models\Nivel;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;

class ServicioDesbloqueo
{
    public function __construct(
        private readonly ServicioAccesoCursos $servicioAccesoCursos,
    ) {}

    public function nivelEstaDesbloqueado(Usuario $usuario, Nivel $nivel): bool
    {
        $nivelActual = Nivel::query()
            ->publicados()
            ->with('curso')
            ->find($nivel->getKey());

        if ($nivelActual === null || ! $this->servicioAccesoCursos->puedeAcceder($usuario, $nivelActual->curso)) {
            return false;
        }

        $nivelesAnteriores = Nivel::query()
            ->publicados()
            ->where('curso_id', $nivelActual->curso_id)
            ->where(fn (Builder $consulta): Builder => $consulta
                ->where('orden', '<', $nivelActual->orden)
                ->orWhere(fn (Builder $empate): Builder => $empate
                    ->where('orden', $nivelActual->orden)
                    ->where('id', '<', $nivelActual->id)))
            ->pluck('id');

        if ($nivelesAnteriores->isEmpty()) {
            return true;
        }

        return ! Modulo::query()
            ->publicados()
            ->whereIn('nivel_id', $nivelesAnteriores)
            ->whereDoesntHave('progresosUsuarios', fn (Builder $progresos): Builder => $progresos
                ->where('usuario_id', $usuario->getKey()))
            ->exists();
    }

    public function moduloEstaDesbloqueado(Usuario $usuario, Modulo $modulo): bool
    {
        $moduloActual = Modulo::query()
            ->publicados()
            ->with('nivel.curso')
            ->find($modulo->getKey());

        if ($moduloActual === null || ! $this->nivelEstaDesbloqueado($usuario, $moduloActual->nivel)) {
            return false;
        }

        return ! Modulo::query()
            ->publicados()
            ->where('nivel_id', $moduloActual->nivel_id)
            ->where(fn (Builder $consulta): Builder => $consulta
                ->where('orden', '<', $moduloActual->orden)
                ->orWhere(fn (Builder $empate): Builder => $empate
                    ->where('orden', $moduloActual->orden)
                    ->where('id', '<', $moduloActual->id)))
            ->whereDoesntHave('progresosUsuarios', fn (Builder $progresos): Builder => $progresos
                ->where('usuario_id', $usuario->getKey()))
            ->exists();
    }
}
