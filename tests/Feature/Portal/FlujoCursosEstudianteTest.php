<?php

namespace Tests\Feature\Portal;

use App\Models\Curso;
use App\Models\GradoAcademico;
use App\Models\ProgresoModuloUsuario;
use Carbon\CarbonImmutable;
use Tests\Feature\Dominio\PruebaDominio;

class FlujoCursosEstudianteTest extends PruebaDominio
{
    public function test_dashboard_muestra_solo_cursos_accesibles_y_no_duplica_asignaciones(): void
    {
        $momento = CarbonImmutable::parse('2026-08-13 10:00:00', 'America/Guatemala');
        $this->travelTo($momento);
        $estudiante = $this->crearEstudianteHabilitado();
        $general = $this->crearCursoPublicado(['titulo' => 'Curso general', 'orden' => 1]);
        $especifico = $this->crearCursoPublicado(['titulo' => 'Curso específico', 'orden' => 2]);
        $combinado = $this->crearCursoPublicado(['titulo' => 'Curso combinado', 'orden' => 3]);
        $futuro = $this->crearCursoPublicado(['titulo' => 'Curso futuro']);
        $vencido = $this->crearCursoPublicado(['titulo' => 'Curso vencido']);
        $inactivo = $this->crearCursoPublicado(['titulo' => 'Curso inactivo']);
        $noPublicado = Curso::factory()->create(['titulo' => 'Curso no publicado', 'publicado' => false]);
        $otroColegio = $this->crearCursoPublicado(['titulo' => 'Curso de otro colegio']);
        $otroGrado = $this->crearCursoPublicado(['titulo' => 'Curso de otro grado']);

        $this->asignarCurso($estudiante, $general);
        $this->asignarCurso($estudiante, $especifico, ['grado_academico_id' => $estudiante->grado_academico_id]);
        $this->asignarCurso($estudiante, $combinado);
        $this->asignarCurso($estudiante, $combinado, ['grado_academico_id' => $estudiante->grado_academico_id]);
        $this->asignarCurso($estudiante, $futuro, ['inicia_en' => $momento->addMinute()]);
        $this->asignarCurso($estudiante, $vencido, ['finaliza_en' => $momento->subMinute()]);
        $this->asignarCurso($estudiante, $inactivo, ['activo' => false]);
        $this->asignarCurso($estudiante, $noPublicado);

        $otroEstudiante = $this->crearEstudianteHabilitado();
        $this->asignarCurso($otroEstudiante, $otroColegio);
        $this->asignarCurso($estudiante, $otroGrado, [
            'grado_academico_id' => GradoAcademico::factory()->create(),
        ]);

        $respuesta = $this->actingAs($estudiante)->get('/dashboard');

        $respuesta
            ->assertSuccessful()
            ->assertSeeText('Curso general')
            ->assertSeeText('Curso específico')
            ->assertSeeText('Curso combinado')
            ->assertDontSeeText('Curso futuro')
            ->assertDontSeeText('Curso vencido')
            ->assertDontSeeText('Curso inactivo')
            ->assertDontSeeText('Curso no publicado')
            ->assertDontSeeText('Curso de otro colegio')
            ->assertDontSeeText('Curso de otro grado');

        $this->assertSame(1, substr_count($respuesta->getContent(), 'data-curso-id="'.$combinado->id.'"'));
    }

    public function test_estudiante_puede_abrir_bienvenida_y_niveles_de_curso_autorizado(): void
    {
        $estudiante = $this->crearEstudianteHabilitado();
        $curso = $this->crearCursoPublicado([
            'titulo' => 'Finanzas para mi futuro',
            'titulo_bienvenida' => '¡Tu aventura financiera comienza!',
            'contenido_bienvenida' => '<p>Aprenderás a tomar mejores decisiones.</p>',
        ]);
        $this->asignarCurso($estudiante, $curso);
        $nivel = $this->crearNivelPublicado($curso);
        $this->crearModuloPublicado($nivel);

        $this->actingAs($estudiante)
            ->get(route('cursos.bienvenida', $curso))
            ->assertSuccessful()
            ->assertSeeText('¡Tu aventura financiera comienza!')
            ->assertSeeText('Aprenderás a tomar mejores decisiones.')
            ->assertSee('Comenzar')
            ->assertSee(route('cursos.niveles', $curso), escape: false);

        $this->actingAs($estudiante)
            ->get(route('cursos.niveles', $curso))
            ->assertSuccessful()
            ->assertSeeText($nivel->titulo);
    }

    public function test_estudiante_no_puede_manipular_url_para_abrir_cursos_no_autorizados(): void
    {
        $estudiante = $this->crearEstudianteHabilitado();
        $otroEstudiante = $this->crearEstudianteHabilitado();
        $cursoOtroColegio = $this->crearCursoPublicado();
        $cursoOtroGrado = $this->crearCursoPublicado();
        $cursoFuturo = $this->crearCursoPublicado();
        $cursoVencido = $this->crearCursoPublicado();
        $cursoInactivo = $this->crearCursoPublicado();
        $cursoNoPublicado = Curso::factory()->create(['publicado' => false]);
        $this->asignarCurso($otroEstudiante, $cursoOtroColegio);
        $this->asignarCurso($estudiante, $cursoOtroGrado, [
            'grado_academico_id' => GradoAcademico::factory()->create(),
        ]);
        $this->asignarCurso($estudiante, $cursoFuturo, ['inicia_en' => now()->addDay()]);
        $this->asignarCurso($estudiante, $cursoVencido, ['finaliza_en' => now()->subDay()]);
        $this->asignarCurso($estudiante, $cursoInactivo, ['activo' => false]);
        $this->asignarCurso($estudiante, $cursoNoPublicado);

        foreach ([$cursoOtroColegio, $cursoOtroGrado, $cursoFuturo, $cursoVencido, $cursoInactivo, $cursoNoPublicado] as $curso) {
            $this->actingAs($estudiante)->get(route('cursos.bienvenida', $curso))->assertForbidden();
            $this->actingAs($estudiante)->get(route('cursos.niveles', $curso))->assertForbidden();
        }
    }

    public function test_dashboard_muestra_progreso_real_solo_de_modulos_publicados(): void
    {
        $estudiante = $this->crearEstudianteHabilitado();
        $curso = $this->crearCursoPublicado(['titulo' => 'Curso con progreso']);
        $this->asignarCurso($estudiante, $curso);
        $nivel = $this->crearNivelPublicado($curso);
        $primerModulo = $this->crearModuloPublicado($nivel);
        $this->crearModuloPublicado($nivel);
        $moduloOculto = $this->crearModuloPublicado($nivel, ['publicado' => false]);
        ProgresoModuloUsuario::factory()->create(['usuario_id' => $estudiante, 'modulo_id' => $primerModulo]);
        ProgresoModuloUsuario::factory()->create(['usuario_id' => $estudiante, 'modulo_id' => $moduloOculto]);

        $this->actingAs($estudiante)
            ->get('/dashboard')
            ->assertSuccessful()
            ->assertSeeText('1 de 2 módulos')
            ->assertSee('aria-valuenow="50"', escape: false);
    }

    public function test_niveles_y_modulos_reflejan_secuencia_inicial(): void
    {
        $estudiante = $this->crearEstudianteHabilitado();
        $curso = $this->crearCursoPublicado();
        $this->asignarCurso($estudiante, $curso);
        $primerNivel = $this->crearNivelPublicado($curso, ['titulo' => 'Nivel inicial', 'orden' => 1]);
        $segundoNivel = $this->crearNivelPublicado($curso, ['titulo' => 'Nivel siguiente', 'orden' => 2]);
        $primerModulo = $this->crearModuloPublicado($primerNivel, ['titulo' => 'Primer módulo', 'orden' => 1]);
        $segundoModulo = $this->crearModuloPublicado($primerNivel, ['titulo' => 'Segundo módulo', 'orden' => 2]);
        $moduloSegundoNivel = $this->crearModuloPublicado($segundoNivel, ['orden' => 1]);

        $this->actingAs($estudiante)
            ->get(route('cursos.niveles', $curso))
            ->assertSuccessful()
            ->assertSee('data-nivel-id="'.$primerNivel->id.'" data-nivel-estado="disponible"', escape: false)
            ->assertSee('data-nivel-id="'.$segundoNivel->id.'" data-nivel-estado="bloqueado"', escape: false)
            ->assertSee('data-modulo-id="'.$primerModulo->id.'" data-modulo-estado="disponible"', escape: false)
            ->assertSee('data-modulo-id="'.$segundoModulo->id.'" data-modulo-estado="bloqueado"', escape: false)
            ->assertSee('data-modulo-id="'.$moduloSegundoNivel->id.'" data-modulo-estado="bloqueado"', escape: false);
    }

    public function test_nivel_completado_desbloquea_el_nivel_siguiente(): void
    {
        $estudiante = $this->crearEstudianteHabilitado();
        $curso = $this->crearCursoPublicado();
        $this->asignarCurso($estudiante, $curso);
        $primerNivel = $this->crearNivelPublicado($curso, ['orden' => 1]);
        $segundoNivel = $this->crearNivelPublicado($curso, ['orden' => 2]);
        $primerModulo = $this->crearModuloPublicado($primerNivel);
        $segundoModulo = $this->crearModuloPublicado($segundoNivel);
        ProgresoModuloUsuario::factory()->create(['usuario_id' => $estudiante, 'modulo_id' => $primerModulo]);

        $this->actingAs($estudiante)
            ->get(route('cursos.niveles', $curso))
            ->assertSuccessful()
            ->assertSee('data-nivel-id="'.$primerNivel->id.'" data-nivel-estado="completado"', escape: false)
            ->assertSee('data-nivel-id="'.$segundoNivel->id.'" data-nivel-estado="disponible"', escape: false)
            ->assertSee('data-modulo-id="'.$primerModulo->id.'" data-modulo-estado="completado"', escape: false)
            ->assertSee('data-modulo-id="'.$segundoModulo->id.'" data-modulo-estado="disponible"', escape: false);
    }

    public function test_dashboard_tiene_estado_vacio_sin_cursos_asignados(): void
    {
        $estudiante = $this->crearEstudianteHabilitado();

        $this->actingAs($estudiante)
            ->get('/dashboard')
            ->assertSuccessful()
            ->assertSeeText('Aún no tienes cursos asignados');
    }

    public function test_curso_sin_contenido_muestra_estado_informativo(): void
    {
        $estudiante = $this->crearEstudianteHabilitado();
        $curso = $this->crearCursoPublicado();
        $this->asignarCurso($estudiante, $curso);

        $this->actingAs($estudiante)
            ->get(route('cursos.bienvenida', $curso))
            ->assertSuccessful()
            ->assertSeeText('Contenido en preparación')
            ->assertDontSee(route('cursos.niveles', $curso), escape: false);

        $this->actingAs($estudiante)
            ->get(route('cursos.niveles', $curso))
            ->assertSuccessful()
            ->assertSeeText('Niveles en preparación');
    }

    public function test_nivel_sin_modulos_muestra_estado_informativo(): void
    {
        $estudiante = $this->crearEstudianteHabilitado();
        $curso = $this->crearCursoPublicado();
        $this->asignarCurso($estudiante, $curso);
        $nivel = $this->crearNivelPublicado($curso);

        $this->actingAs($estudiante)
            ->get(route('cursos.niveles', $curso))
            ->assertSuccessful()
            ->assertSeeText($nivel->titulo)
            ->assertSeeText('Módulos en preparación');
    }

    public function test_visitante_no_puede_acceder_a_bienvenida_ni_niveles(): void
    {
        $curso = $this->crearCursoPublicado();

        $this->get(route('cursos.bienvenida', $curso))->assertRedirect('/login');
        $this->get(route('cursos.niveles', $curso))->assertRedirect('/login');
    }
}
