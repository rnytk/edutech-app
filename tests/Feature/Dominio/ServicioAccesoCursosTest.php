<?php

namespace Tests\Feature\Dominio;

use App\Models\AsignacionCurso;
use App\Models\Curso;
use App\Models\GradoAcademico;
use App\Models\Usuario;
use App\Services\ServicioAccesoCursos;
use Carbon\CarbonImmutable;

class ServicioAccesoCursosTest extends PruebaDominio
{
    public function test_devuelve_solo_cursos_publicados_con_asignaciones_aplicables_activas_y_vigentes(): void
    {
        $momento = CarbonImmutable::parse('2026-08-13 10:00:00', 'America/Guatemala');
        $usuario = $this->crearEstudianteHabilitado();
        $cursoGeneral = $this->crearCursoPublicado(['orden' => 1]);
        $cursoEspecifico = $this->crearCursoPublicado(['orden' => 2]);
        $cursoFuturo = $this->crearCursoPublicado(['orden' => 3]);
        $cursoVencido = $this->crearCursoPublicado(['orden' => 4]);
        $cursoInactivo = $this->crearCursoPublicado(['orden' => 5]);
        $cursoNoPublicado = Curso::factory()->create(['publicado' => false]);
        $cursoOtroGrado = $this->crearCursoPublicado(['orden' => 6]);

        $this->asignarCurso($usuario, $cursoGeneral, [
            'inicia_en' => null,
            'finaliza_en' => null,
        ]);
        $this->asignarCurso($usuario, $cursoGeneral, [
            'grado_academico_id' => $usuario->grado_academico_id,
            'inicia_en' => null,
            'finaliza_en' => null,
        ]);
        $this->asignarCurso($usuario, $cursoEspecifico, [
            'grado_academico_id' => $usuario->grado_academico_id,
            'inicia_en' => $momento,
            'finaliza_en' => $momento,
        ]);
        $this->asignarCurso($usuario, $cursoFuturo, [
            'inicia_en' => $momento->addSecond(),
            'finaliza_en' => null,
        ]);
        $this->asignarCurso($usuario, $cursoVencido, [
            'inicia_en' => null,
            'finaliza_en' => $momento->subSecond(),
        ]);
        $this->asignarCurso($usuario, $cursoInactivo, [
            'activo' => false,
            'inicia_en' => null,
            'finaliza_en' => null,
        ]);
        $this->asignarCurso($usuario, $cursoNoPublicado, [
            'inicia_en' => null,
            'finaliza_en' => null,
        ]);
        $otroGrado = GradoAcademico::factory()->create();
        $this->asignarCurso($usuario, $cursoOtroGrado, [
            'grado_academico_id' => $otroGrado,
            'inicia_en' => null,
            'finaliza_en' => null,
        ]);

        $cursos = app(ServicioAccesoCursos::class)->obtenerCursosDisponibles($usuario, $momento);

        $this->assertSame([$cursoGeneral->id, $cursoEspecifico->id], $cursos->modelKeys());
    }

    public function test_una_asignacion_no_aplica_a_otro_colegio(): void
    {
        $usuario = $this->crearEstudianteHabilitado();
        $curso = $this->crearCursoPublicado();
        $otroUsuario = $this->crearEstudianteHabilitado();

        $this->asignarCurso($otroUsuario, $curso, [
            'inicia_en' => null,
            'finaliza_en' => null,
        ]);

        $this->assertFalse(app(ServicioAccesoCursos::class)->puedeAcceder($usuario, $curso));
    }

    public function test_rechaza_estudiante_usuario_colegio_o_grado_inactivos(): void
    {
        $servicio = app(ServicioAccesoCursos::class);

        $usuarioInactivo = $this->crearEstudianteHabilitado(['activo' => false]);
        $cursoUsuarioInactivo = $this->crearCursoPublicado();
        $this->asignarCurso($usuarioInactivo, $cursoUsuarioInactivo);

        $usuarioColegioInactivo = $this->crearEstudianteHabilitado();
        $usuarioColegioInactivo->colegio()->update(['activo' => false]);
        $cursoColegioInactivo = $this->crearCursoPublicado();
        $this->asignarCurso($usuarioColegioInactivo, $cursoColegioInactivo);

        $usuarioGradoInactivo = $this->crearEstudianteHabilitado();
        $usuarioGradoInactivo->gradoAcademico()->update(['activo' => false]);
        $cursoGradoInactivo = $this->crearCursoPublicado();
        $this->asignarCurso($usuarioGradoInactivo, $cursoGradoInactivo);

        $this->assertFalse($servicio->puedeAcceder($usuarioInactivo, $cursoUsuarioInactivo));
        $this->assertFalse($servicio->puedeAcceder($usuarioColegioInactivo, $cursoColegioInactivo));
        $this->assertFalse($servicio->puedeAcceder($usuarioGradoInactivo, $cursoGradoInactivo));

        $usuarioPersistido = $this->crearEstudianteHabilitado();
        $cursoUsuarioPersistido = $this->crearCursoPublicado();
        $this->asignarCurso($usuarioPersistido, $cursoUsuarioPersistido);
        Usuario::query()->whereKey($usuarioPersistido)->update(['activo' => false]);

        $this->assertTrue($usuarioPersistido->activo);
        $this->assertFalse($servicio->puedeAcceder($usuarioPersistido, $cursoUsuarioPersistido));
    }

    public function test_scope_vigentes_respeta_limites_inclusivos_y_valores_nulos(): void
    {
        $momento = CarbonImmutable::parse('2026-08-13 10:00:00', 'America/Guatemala');
        $usuario = $this->crearEstudianteHabilitado();
        $curso = $this->crearCursoPublicado();
        $asignacion = $this->asignarCurso($usuario, $curso, [
            'inicia_en' => $momento,
            'finaliza_en' => $momento,
        ]);

        $this->assertTrue(AsignacionCurso::query()->vigentes($momento)->whereKey($asignacion)->exists());
        $this->assertFalse(AsignacionCurso::query()->vigentes($momento->subSecond())->whereKey($asignacion)->exists());
        $this->assertFalse(AsignacionCurso::query()->vigentes($momento->addSecond())->whereKey($asignacion)->exists());
    }
}
