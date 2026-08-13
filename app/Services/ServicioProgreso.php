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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\JoinClause;
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

    /**
     * @param  Collection<int, Curso>  $cursos
     * @return array<int, array{completados: int, total: int, porcentaje: int, completado: bool}>
     */
    public function resumirCursos(Usuario $usuario, Collection $cursos): array
    {
        return $this->resumirAgrupados(
            $usuario,
            'niveles.curso_id',
            $cursos->modelKeys(),
        );
    }

    /**
     * @param  Collection<int, Nivel>  $niveles
     * @return array<int, array{completados: int, total: int, porcentaje: int, completado: bool}>
     */
    public function resumirNiveles(Usuario $usuario, Collection $niveles): array
    {
        return $this->resumirAgrupados(
            $usuario,
            'modulos.nivel_id',
            $niveles->modelKeys(),
        );
    }

    /**
     * @param  Collection<int, Modulo>  $modulos
     * @return array<int, true>
     */
    public function obtenerModulosCompletados(Usuario $usuario, Collection $modulos): array
    {
        if ($modulos->isEmpty()) {
            return [];
        }

        return ProgresoModuloUsuario::query()
            ->where('usuario_id', $usuario->getKey())
            ->whereIn('modulo_id', $modulos->modelKeys())
            ->pluck('modulo_id')
            ->mapWithKeys(fn (int $moduloId): array => [$moduloId => true])
            ->all();
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

    /**
     * @param  array<int, int|string>  $identificadores
     * @return array<int, array{completados: int, total: int, porcentaje: int, completado: bool}>
     */
    private function resumirAgrupados(
        Usuario $usuario,
        string $columnaAgrupacion,
        array $identificadores,
    ): array {
        $resumenes = [];

        foreach ($identificadores as $identificador) {
            $resumenes[(int) $identificador] = $this->crearResumen(0, 0);
        }

        if ($identificadores === []) {
            return $resumenes;
        }

        $filas = Modulo::query()
            ->join('niveles', 'niveles.id', '=', 'modulos.nivel_id')
            ->leftJoin('progreso_modulos_usuario as progresos', function (JoinClause $union) use ($usuario): void {
                $union->on('progresos.modulo_id', '=', 'modulos.id')
                    ->where('progresos.usuario_id', '=', $usuario->getKey());
            })
            ->where('modulos.publicado', true)
            ->where('niveles.publicado', true)
            ->whereIn($columnaAgrupacion, $identificadores)
            ->groupBy($columnaAgrupacion)
            ->selectRaw("{$columnaAgrupacion} as agrupador")
            ->selectRaw('COUNT(modulos.id) as total')
            ->selectRaw('COUNT(progresos.id) as completados')
            ->get();

        foreach ($filas as $fila) {
            $resumenes[(int) $fila->agrupador] = $this->crearResumen(
                (int) $fila->completados,
                (int) $fila->total,
            );
        }

        return $resumenes;
    }

    /** @return array{completados: int, total: int, porcentaje: int, completado: bool} */
    private function crearResumen(int $completados, int $total): array
    {
        return [
            'completados' => $completados,
            'total' => $total,
            'porcentaje' => $this->calcularPorcentaje($completados, $total),
            'completado' => $total > 0 && $completados === $total,
        ];
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
