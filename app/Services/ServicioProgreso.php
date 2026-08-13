<?php

namespace App\Services;

use App\Enums\TipoActividad;
use App\Models\Curso;
use App\Models\IntentoActividad;
use App\Models\Modulo;
use App\Models\Nivel;
use App\Models\ProgresoModuloUsuario;
use App\Models\Usuario;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ServicioProgreso
{
    public function __construct(
        private readonly ServicioDesbloqueo $servicioDesbloqueo,
    ) {}

    public function moduloEstaCompletado(Usuario $usuario, Modulo $modulo): bool
    {
        return ProgresoModuloUsuario::query()
            ->where('usuario_id', $usuario->getKey())
            ->where('modulo_id', $modulo->getKey())
            ->exists();
    }

    public function nivelEstaCompletado(Usuario $usuario, Nivel $nivel): bool
    {
        [$completados, $total] = $this->conteosNivel($usuario, $nivel);

        return $total > 0 && $completados === $total;
    }

    public function cursoEstaCompletado(Usuario $usuario, Curso $curso): bool
    {
        [$completados, $total] = $this->conteosCurso($usuario, $curso);

        return $total > 0 && $completados === $total;
    }

    public function calcularPorcentajeNivel(Usuario $usuario, Nivel $nivel): int
    {
        [$completados, $total] = $this->conteosNivel($usuario, $nivel);

        return $this->calcularPorcentaje($completados, $total);
    }

    public function calcularPorcentajeCurso(Usuario $usuario, Curso $curso): int
    {
        [$completados, $total] = $this->conteosCurso($usuario, $curso);

        return $this->calcularPorcentaje($completados, $total);
    }

    public function puedeFinalizarModulo(Usuario $usuario, Modulo $modulo): bool
    {
        if ($this->moduloEstaCompletado($usuario, $modulo)) {
            return true;
        }

        if (! $this->servicioDesbloqueo->moduloEstaDesbloqueado($usuario, $modulo)) {
            return false;
        }

        $moduloActual = Modulo::query()->find($modulo->getKey());

        if ($moduloActual === null) {
            return false;
        }

        $actividadesRequeridas = $this->obtenerActividadesRequeridas($moduloActual);

        if ($actividadesRequeridas === null) {
            return false;
        }

        if ($actividadesRequeridas === []) {
            return true;
        }

        $intentos = IntentoActividad::query()
            ->where('usuario_id', $usuario->getKey())
            ->where('modulo_id', $moduloActual->getKey())
            ->whereIn('actividad_uuid', array_keys($actividadesRequeridas))
            ->get(['actividad_uuid', 'tipo_actividad', 'correcta'])
            ->groupBy('actividad_uuid');

        foreach ($actividadesRequeridas as $actividadUuid => $tipoActividad) {
            $intentosActividad = $intentos->get($actividadUuid, collect());

            $completada = $intentosActividad->contains(
                fn (IntentoActividad $intento): bool => $intento->tipo_actividad === $tipoActividad->value
                    && ($tipoActividad === TipoActividad::RespuestaDirecta || $intento->correcta === true),
            );

            if (! $completada) {
                return false;
            }
        }

        return true;
    }

    public function finalizarModulo(Usuario $usuario, Modulo $modulo): ProgresoModuloUsuario
    {
        return DB::transaction(function () use ($usuario, $modulo): ProgresoModuloUsuario {
            $progresoExistente = ProgresoModuloUsuario::query()
                ->where('usuario_id', $usuario->getKey())
                ->where('modulo_id', $modulo->getKey())
                ->first();

            if ($progresoExistente !== null) {
                return $progresoExistente;
            }

            if (! $this->puedeFinalizarModulo($usuario, $modulo)) {
                throw new DomainException('El módulo todavía no cumple las condiciones de finalización.');
            }

            return ProgresoModuloUsuario::query()->firstOrCreate(
                [
                    'usuario_id' => $usuario->getKey(),
                    'modulo_id' => $modulo->getKey(),
                ],
                ['completado_en' => now()],
            );
        });
    }

    /** @return array{0: int, 1: int} */
    private function conteosNivel(Usuario $usuario, Nivel $nivel): array
    {
        $consulta = Modulo::query()
            ->publicados()
            ->where('nivel_id', $nivel->getKey());

        return $this->obtenerConteos($consulta, $usuario);
    }

    /** @return array{0: int, 1: int} */
    private function conteosCurso(Usuario $usuario, Curso $curso): array
    {
        $consulta = Modulo::query()
            ->publicados()
            ->whereHas('nivel', fn (Builder $niveles): Builder => $niveles
                ->publicados()
                ->where('curso_id', $curso->getKey()));

        return $this->obtenerConteos($consulta, $usuario);
    }

    /** @return array{0: int, 1: int} */
    private function obtenerConteos(Builder $consulta, Usuario $usuario): array
    {
        $total = (clone $consulta)->count();
        $completados = (clone $consulta)
            ->whereHas('progresosUsuarios', fn (Builder $progresos): Builder => $progresos
                ->where('usuario_id', $usuario->getKey()))
            ->count();

        return [$completados, $total];
    }

    private function calcularPorcentaje(int $completados, int $total): int
    {
        if ($total === 0) {
            return 0;
        }

        return (int) round(($completados / $total) * 100);
    }

    /** @return array<string, TipoActividad>|null */
    private function obtenerActividadesRequeridas(Modulo $modulo): ?array
    {
        $actividadesRequeridas = [];

        foreach ($modulo->actividades as $actividad) {
            if (! is_array($actividad)) {
                return null;
            }

            $actividadUuid = $actividad['uuid'] ?? null;
            $tipoActividad = TipoActividad::tryFrom($actividad['tipo'] ?? '');

            if (! is_string($actividadUuid) || $actividadUuid === '' || $tipoActividad === null) {
                return null;
            }

            if (array_key_exists($actividadUuid, $actividadesRequeridas)) {
                return null;
            }

            $actividadesRequeridas[$actividadUuid] = $tipoActividad;
        }

        return $actividadesRequeridas;
    }
}
