<?php

namespace App\Services;

use App\Enums\RolUsuario;
use App\Models\AsignacionCurso;
use App\Models\Curso;
use App\Models\Usuario;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ServicioAccesoCursos
{
    public function usuarioPuedeConsumirContenido(Usuario $usuario): bool
    {
        return $this->obtenerEstudianteHabilitado($usuario) !== null;
    }

    /** @return Collection<int, Curso> */
    public function obtenerCursosDisponibles(Usuario $usuario, ?CarbonInterface $momento = null): Collection
    {
        $estudiante = $this->obtenerEstudianteHabilitado($usuario);

        if ($estudiante === null) {
            return new Collection;
        }

        return Curso::query()
            ->publicados()
            ->whereHas('asignacionesCursos', fn (Builder $asignaciones): Builder => $asignaciones
                ->aplicablesA($estudiante)
                ->vigentes($momento))
            ->orderBy('orden')
            ->orderBy('id')
            ->get();
    }

    public function puedeAcceder(
        Usuario $usuario,
        Curso $curso,
        ?CarbonInterface $momento = null,
    ): bool {
        $estudiante = $this->obtenerEstudianteHabilitado($usuario);

        if ($estudiante === null) {
            return false;
        }

        $cursoPublicado = Curso::query()
            ->publicados()
            ->whereKey($curso->getKey())
            ->exists();

        return $cursoPublicado && $this->existeAsignacionVigente($estudiante, $curso, $momento);
    }

    public function tieneAsignacionVigente(
        Usuario $usuario,
        Curso $curso,
        ?CarbonInterface $momento = null,
    ): bool {
        $estudiante = $this->obtenerEstudianteHabilitado($usuario);

        if ($estudiante === null) {
            return false;
        }

        return $this->existeAsignacionVigente($estudiante, $curso, $momento);
    }

    private function obtenerEstudianteHabilitado(Usuario $usuario): ?Usuario
    {
        $estudiante = Usuario::query()
            ->with(['colegio:id,activo', 'gradoAcademico:id,activo'])
            ->find($usuario->getKey());

        if ($estudiante === null
            || ! $estudiante->activo
            || $estudiante->rol !== RolUsuario::Estudiante
            || $estudiante->colegio_id === null
            || $estudiante->grado_academico_id === null
            || $estudiante->colegio?->activo !== true
            || $estudiante->gradoAcademico?->activo !== true) {
            return null;
        }

        return $estudiante;
    }

    private function existeAsignacionVigente(
        Usuario $estudiante,
        Curso $curso,
        ?CarbonInterface $momento,
    ): bool {

        return AsignacionCurso::query()
            ->where('curso_id', $curso->getKey())
            ->aplicablesA($estudiante)
            ->vigentes($momento)
            ->exists();
    }
}
