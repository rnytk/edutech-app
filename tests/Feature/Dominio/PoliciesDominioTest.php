<?php

namespace Tests\Feature\Dominio;

use App\Models\Curso;
use App\Models\IntentoActividad;
use App\Models\ProgresoModuloUsuario;
use App\Models\Usuario;
use Illuminate\Support\Facades\Gate;

class PoliciesDominioTest extends PruebaDominio
{
    public function test_policy_de_curso_exige_asignacion_para_estudiante_y_admite_superadministrador_activo(): void
    {
        $estudiante = $this->crearEstudianteHabilitado();
        $cursoAsignado = $this->crearCursoPublicado();
        $cursoNoAsignado = $this->crearCursoPublicado();
        $cursoNoPublicado = $this->crearCursoPublicado(['publicado' => false]);
        $this->asignarCurso($estudiante, $cursoAsignado);
        $superadministrador = Usuario::factory()->superadministrador()->create();
        $superadministradorInactivo = Usuario::factory()->superadministrador()->inactivo()->create();

        $this->assertTrue(Gate::forUser($estudiante)->allows('viewAny', Curso::class));
        $this->assertTrue(Gate::forUser($estudiante)->allows('view', $cursoAsignado));
        $this->assertFalse(Gate::forUser($estudiante)->allows('view', $cursoNoAsignado));
        $this->assertFalse(Gate::forUser($estudiante)->allows('view', $cursoNoPublicado));
        $this->assertTrue(Gate::forUser($superadministrador)->allows('view', $cursoNoPublicado));
        $this->assertFalse(Gate::forUser($superadministradorInactivo)->allows('view', $cursoNoPublicado));
    }

    public function test_policies_de_nivel_y_modulo_aplican_la_secuencia_en_servidor(): void
    {
        $estudiante = $this->crearEstudianteHabilitado();
        $curso = $this->crearCursoPublicado();
        $this->asignarCurso($estudiante, $curso);
        $nivel = $this->crearNivelPublicado($curso);
        $primerModulo = $this->crearModuloPublicado($nivel, ['orden' => 1]);
        $segundoModulo = $this->crearModuloPublicado($nivel, ['orden' => 2]);

        $this->assertTrue(Gate::forUser($estudiante)->allows('view', $nivel));
        $this->assertTrue(Gate::forUser($estudiante)->allows('view', $primerModulo));
        $this->assertFalse(Gate::forUser($estudiante)->allows('view', $segundoModulo));
    }

    public function test_historial_solo_puede_consultarse_por_propietario_o_superadministrador_y_nunca_modificarse(): void
    {
        $propietario = $this->crearEstudianteHabilitado();
        $otroEstudiante = $this->crearEstudianteHabilitado();
        $superadministrador = Usuario::factory()->superadministrador()->create();
        $curso = $this->crearCursoPublicado();
        $nivel = $this->crearNivelPublicado($curso);
        $modulo = $this->crearModuloPublicado($nivel);
        $intento = IntentoActividad::factory()->create([
            'usuario_id' => $propietario,
            'modulo_id' => $modulo,
        ]);
        $progreso = ProgresoModuloUsuario::factory()->create([
            'usuario_id' => $propietario,
            'modulo_id' => $modulo,
        ]);

        $this->assertTrue(Gate::forUser($propietario)->allows('view', $intento));
        $this->assertTrue(Gate::forUser($propietario)->allows('view', $progreso));
        $this->assertFalse(Gate::forUser($propietario)->allows('viewAny', IntentoActividad::class));
        $this->assertFalse(Gate::forUser($propietario)->allows('viewAny', ProgresoModuloUsuario::class));
        $this->assertFalse(Gate::forUser($otroEstudiante)->allows('view', $intento));
        $this->assertFalse(Gate::forUser($otroEstudiante)->allows('view', $progreso));
        $this->assertTrue(Gate::forUser($superadministrador)->allows('view', $intento));
        $this->assertTrue(Gate::forUser($superadministrador)->allows('view', $progreso));
        $this->assertTrue(Gate::forUser($superadministrador)->allows('viewAny', IntentoActividad::class));
        $this->assertTrue(Gate::forUser($superadministrador)->allows('viewAny', ProgresoModuloUsuario::class));
        $this->assertFalse(Gate::forUser($superadministrador)->allows('update', $intento));
        $this->assertFalse(Gate::forUser($superadministrador)->allows('delete', $intento));
        $this->assertFalse(Gate::forUser($superadministrador)->allows('update', $progreso));
        $this->assertFalse(Gate::forUser($superadministrador)->allows('delete', $progreso));
    }

    public function test_usuario_inactivo_no_puede_consultar_historial_propio(): void
    {
        $usuario = $this->crearEstudianteHabilitado(['activo' => false]);
        $intento = IntentoActividad::factory()->create(['usuario_id' => $usuario]);
        $progreso = ProgresoModuloUsuario::factory()->create(['usuario_id' => $usuario]);

        $this->assertFalse(Gate::forUser($usuario)->allows('view', $intento));
        $this->assertFalse(Gate::forUser($usuario)->allows('view', $progreso));
    }
}
