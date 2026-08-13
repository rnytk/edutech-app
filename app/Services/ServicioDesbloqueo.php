<?php

namespace App\Services;

use App\Models\Curso;
use App\Models\Modulo;
use App\Models\Nivel;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

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

    /**
     * @param  Collection<int, Nivel>  $niveles
     * @param  array<int, true>  $modulosCompletados
     * @return array{niveles: array<int, bool>, modulos: array<int, bool>}
     */
    public function calcularEstadosCurso(
        Usuario $usuario,
        Curso $curso,
        Collection $niveles,
        array $modulosCompletados,
    ): array {
        $estados = ['niveles' => [], 'modulos' => []];
        $nivelDisponible = $this->servicioAccesoCursos->puedeAcceder($usuario, $curso);

        foreach ($niveles as $nivel) {
            $estados['niveles'][$nivel->getKey()] = $nivelDisponible;
            $moduloDisponible = $nivelDisponible;

            foreach ($nivel->modulos as $modulo) {
                $estados['modulos'][$modulo->getKey()] = $moduloDisponible;

                if (! isset($modulosCompletados[$modulo->getKey()])) {
                    $moduloDisponible = false;
                }
            }

            if (! $moduloDisponible) {
                $nivelDisponible = false;
            }
        }

        return $estados;
    }
}
