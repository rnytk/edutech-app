<?php

namespace Tests\Feature\BaseDatos;

use App\Enums\RolUsuario;
use App\Models\AsignacionCurso;
use App\Models\Capsula;
use App\Models\Colegio;
use App\Models\Curso;
use App\Models\GradoAcademico;
use App\Models\IntentoActividad;
use App\Models\Modulo;
use App\Models\Nivel;
use App\Models\ProgresoModuloUsuario;
use App\Models\Usuario;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class EstructuraDominioTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::connection()->getDatabaseName() !== 'edutech_app_testing') {
            throw new RuntimeException('Las pruebas de base de datos solo pueden ejecutarse en edutech_app_testing.');
        }
    }

    public function test_existen_las_tablas_y_columnas_del_dominio_en_espanol(): void
    {
        $columnasPorTabla = [
            'colegios' => ['id', 'nombre', 'codigo', 'activo', 'creado_en', 'actualizado_en'],
            'grados_academicos' => ['id', 'nombre', 'codigo', 'orden', 'activo', 'creado_en', 'actualizado_en'],
            'usuarios' => ['id', 'nombre', 'correo_electronico', 'contrasena', 'rol', 'colegio_id', 'grado_academico_id', 'activo', 'token_recordatorio', 'creado_en', 'actualizado_en'],
            'cursos' => ['id', 'titulo', 'descripcion', 'ruta_imagen', 'orden', 'publicado', 'creado_en', 'actualizado_en'],
            'niveles' => ['id', 'curso_id', 'titulo', 'descripcion', 'ruta_imagen', 'orden', 'publicado', 'creado_en', 'actualizado_en'],
            'modulos' => ['id', 'nivel_id', 'titulo', 'descripcion', 'ruta_imagen', 'orden', 'bloques_contenido', 'actividades', 'mensaje_cierre', 'publicado', 'creado_en', 'actualizado_en'],
            'capsulas' => ['id', 'modulo_id', 'titulo', 'contenido', 'ruta_imagen', 'orden', 'activo', 'creado_en', 'actualizado_en'],
            'asignaciones_cursos' => ['id', 'curso_id', 'colegio_id', 'grado_academico_id', 'activo', 'inicia_en', 'finaliza_en', 'creado_en', 'actualizado_en'],
            'intentos_actividades' => ['id', 'usuario_id', 'modulo_id', 'actividad_uuid', 'tipo_actividad', 'numero_intento', 'respuesta', 'correcta', 'respondido_en', 'creado_en', 'actualizado_en'],
            'progreso_modulos_usuario' => ['id', 'usuario_id', 'modulo_id', 'completado_en', 'creado_en', 'actualizado_en'],
        ];

        foreach ($columnasPorTabla as $tabla => $columnas) {
            $this->assertTrue(Schema::hasTable($tabla), "No existe la tabla {$tabla}.");
            $this->assertTrue(Schema::hasColumns($tabla, $columnas), "Faltan columnas en {$tabla}.");
            $this->assertFalse(Schema::hasColumn($tabla, 'created_at'));
            $this->assertFalse(Schema::hasColumn($tabla, 'updated_at'));
        }

        foreach (['schools', 'academic_degrees', 'users', 'courses', 'levels', 'modules'] as $tablaInglesa) {
            $this->assertFalse(Schema::hasTable($tablaInglesa));
        }
    }

    public function test_los_modelos_declaran_tablas_guarded_y_timestamps_personalizados(): void
    {
        $modelos = [
            Colegio::class => 'colegios',
            GradoAcademico::class => 'grados_academicos',
            Usuario::class => 'usuarios',
            Curso::class => 'cursos',
            Nivel::class => 'niveles',
            Modulo::class => 'modulos',
            Capsula::class => 'capsulas',
            AsignacionCurso::class => 'asignaciones_cursos',
            IntentoActividad::class => 'intentos_actividades',
            ProgresoModuloUsuario::class => 'progreso_modulos_usuario',
        ];

        foreach ($modelos as $clase => $tabla) {
            /** @var Model $modelo */
            $modelo = new $clase;

            $this->assertSame($tabla, $modelo->getTable());
            $this->assertSame([], $modelo->getGuarded());
            $this->assertSame('creado_en', $modelo->getCreatedAtColumn());
            $this->assertSame('actualizado_en', $modelo->getUpdatedAtColumn());
        }
    }

    public function test_las_factories_generan_entidades_validas_y_las_relaciones_funcionan_en_ambos_sentidos(): void
    {
        $colegio = Colegio::factory()->create();
        $grado = GradoAcademico::factory()->create();
        $usuario = Usuario::factory()->create([
            'colegio_id' => $colegio,
            'grado_academico_id' => $grado,
        ]);
        $curso = Curso::factory()->create();
        $nivel = Nivel::factory()->create(['curso_id' => $curso]);
        $modulo = Modulo::factory()->create(['nivel_id' => $nivel]);
        $capsula = Capsula::factory()->create(['modulo_id' => $modulo]);
        $asignacion = AsignacionCurso::factory()->create([
            'curso_id' => $curso,
            'colegio_id' => $colegio,
            'grado_academico_id' => $grado,
        ]);
        $intento = IntentoActividad::factory()->create([
            'usuario_id' => $usuario,
            'modulo_id' => $modulo,
        ]);
        $progreso = ProgresoModuloUsuario::factory()->create([
            'usuario_id' => $usuario,
            'modulo_id' => $modulo,
        ]);

        $this->assertTrue($colegio->usuarios->contains($usuario));
        $this->assertTrue($colegio->asignacionesCursos->contains($asignacion));
        $this->assertTrue($grado->usuarios->contains($usuario));
        $this->assertTrue($grado->asignacionesCursos->contains($asignacion));
        $this->assertTrue($usuario->colegio->is($colegio));
        $this->assertTrue($usuario->gradoAcademico->is($grado));
        $this->assertTrue($usuario->intentosActividades->contains($intento));
        $this->assertTrue($usuario->progresosModulos->contains($progreso));
        $this->assertTrue($curso->niveles->contains($nivel));
        $this->assertTrue($curso->asignacionesCursos->contains($asignacion));
        $this->assertTrue($nivel->curso->is($curso));
        $this->assertTrue($nivel->modulos->contains($modulo));
        $this->assertTrue($modulo->nivel->is($nivel));
        $this->assertTrue($modulo->capsulas->contains($capsula));
        $this->assertTrue($modulo->intentosActividades->contains($intento));
        $this->assertTrue($modulo->progresosUsuarios->contains($progreso));
        $this->assertTrue($capsula->modulo->is($modulo));
        $this->assertTrue($asignacion->curso->is($curso));
        $this->assertTrue($asignacion->colegio->is($colegio));
        $this->assertTrue($asignacion->gradoAcademico->is($grado));
        $this->assertTrue($intento->usuario->is($usuario));
        $this->assertTrue($intento->modulo->is($modulo));
        $this->assertTrue($progreso->usuario->is($usuario));
        $this->assertTrue($progreso->modulo->is($modulo));
    }

    public function test_los_casts_jsonb_booleanos_fechas_y_enum_son_correctos(): void
    {
        $bloques = [['uuid' => (string) Str::uuid(), 'tipo' => 'tarjeta', 'titulo' => 'Ahorro']];
        $actividades = [['uuid' => (string) Str::uuid(), 'tipo' => 'falso_verdadero', 'pregunta' => '¿Ahorrar es útil?', 'respuesta_correcta' => true]];
        $modulo = Modulo::factory()->create([
            'bloques_contenido' => $bloques,
            'actividades' => $actividades,
            'publicado' => true,
        ])->refresh();
        $usuario = Usuario::factory()->create(['activo' => true])->refresh();
        $intento = IntentoActividad::factory()->create([
            'usuario_id' => $usuario,
            'modulo_id' => $modulo,
            'respuesta' => ['valor' => true],
            'correcta' => true,
        ])->refresh();

        $this->assertEquals($bloques, $modulo->bloques_contenido);
        $this->assertEquals($actividades, $modulo->actividades);
        $this->assertTrue($modulo->publicado);
        $this->assertTrue($usuario->activo);
        $this->assertSame(RolUsuario::Estudiante, $usuario->rol);
        $this->assertSame(['valor' => true], $intento->respuesta);
        $this->assertTrue($intento->correcta);
        $this->assertInstanceOf(CarbonImmutable::class, $modulo->creado_en);
        $this->assertInstanceOf(CarbonImmutable::class, $intento->respondido_en);
    }

    public function test_usuario_esta_preparado_para_los_campos_de_autenticacion_en_espanol(): void
    {
        $usuario = new Usuario;

        $this->assertSame(Usuario::class, config('auth.providers.users.model'));
        $this->assertSame('contrasena', $usuario->getAuthPasswordName());
        $this->assertSame('token_recordatorio', $usuario->getRememberTokenName());
    }

    public function test_postgresql_utiliza_jsonb_indices_y_restricciones_esperadas(): void
    {
        $tiposJsonb = collect(DB::select(<<<'SQL'
            SELECT table_name, column_name, udt_name
            FROM information_schema.columns
            WHERE table_schema = 'public'
              AND (table_name, column_name) IN (
                  ('modulos', 'bloques_contenido'),
                  ('modulos', 'actividades'),
                  ('intentos_actividades', 'respuesta')
              )
            ORDER BY table_name, column_name
        SQL));

        $this->assertCount(3, $tiposJsonb);
        $this->assertTrue($tiposJsonb->every(fn (object $columna): bool => $columna->udt_name === 'jsonb'));

        $indices = collect(DB::select(<<<'SQL'
            SELECT indexname, indexdef
            FROM pg_indexes
            WHERE schemaname = 'public'
        SQL))->keyBy('indexname');

        foreach ([
            'colegios_codigo_unico',
            'grados_academicos_codigo_unico',
            'usuarios_correo_electronico_unico',
            'usuarios_colegio_grado_activo_indice',
            'cursos_publicado_orden_indice',
            'niveles_curso_publicado_orden_indice',
            'modulos_nivel_publicado_orden_indice',
            'capsulas_modulo_activo_orden_indice',
            'asignaciones_cursos_acceso_indice',
            'asignaciones_cursos_curso_activo_indice',
            'intentos_usuario_modulo_actividad_indice',
            'intentos_modulo_tipo_indice',
            'progreso_usuario_modulo_unico',
            'progreso_modulo_indice',
        ] as $indice) {
            $this->assertTrue($indices->has($indice), "No existe el índice {$indice}.");
        }

        $general = strtolower($indices->get('asignaciones_cursos_general_unica')->indexdef);
        $especifica = strtolower($indices->get('asignaciones_cursos_especifica_unica')->indexdef);

        $this->assertStringContainsString('unique index', $general);
        $this->assertStringContainsString('grado_academico_id is null', $general);
        $this->assertStringContainsString('unique index', $especifica);
        $this->assertStringContainsString('grado_academico_id is not null', $especifica);

        $restricciones = collect(DB::select(<<<'SQL'
            SELECT con.conname
            FROM pg_constraint con
            JOIN pg_namespace nsp ON nsp.oid = con.connamespace
            WHERE nsp.nspname = 'public'
        SQL))->pluck('conname');

        foreach ([
            'usuarios_rol_valido',
            'asignaciones_cursos_fechas_validas',
            'intentos_actividades_numero_positivo',
            'intentos_actividades_tipo_valido',
        ] as $restriccion) {
            $this->assertContains($restriccion, $restricciones);
        }
    }

    public function test_todas_las_claves_foraneas_restringen_el_borrado(): void
    {
        $clavesForaneas = collect(DB::select(<<<'SQL'
            SELECT con.conname, con.confdeltype
            FROM pg_constraint con
            JOIN pg_class tabla ON tabla.oid = con.conrelid
            JOIN pg_namespace esquema ON esquema.oid = tabla.relnamespace
            WHERE con.contype = 'f'
              AND esquema.nspname = 'public'
              AND tabla.relname IN (
                  'usuarios', 'niveles', 'modulos', 'capsulas',
                  'asignaciones_cursos', 'intentos_actividades',
                  'progreso_modulos_usuario'
              )
        SQL));

        $this->assertCount(12, $clavesForaneas);
        $this->assertTrue($clavesForaneas->every(fn (object $clave): bool => $clave->confdeltype === 'r'));
    }

    public function test_una_asignacion_general_y_una_especifica_pueden_coexistir(): void
    {
        $curso = Curso::factory()->create();
        $colegio = Colegio::factory()->create();
        $grado = GradoAcademico::factory()->create();

        AsignacionCurso::factory()->create([
            'curso_id' => $curso,
            'colegio_id' => $colegio,
            'grado_academico_id' => null,
        ]);
        AsignacionCurso::factory()->create([
            'curso_id' => $curso,
            'colegio_id' => $colegio,
            'grado_academico_id' => $grado,
        ]);

        $this->assertSame(2, AsignacionCurso::query()->count());
    }

    public function test_no_permite_asignaciones_generales_duplicadas(): void
    {
        $asignacion = AsignacionCurso::factory()->create(['grado_academico_id' => null]);

        $this->expectException(QueryException::class);

        AsignacionCurso::factory()->create([
            'curso_id' => $asignacion->curso_id,
            'colegio_id' => $asignacion->colegio_id,
            'grado_academico_id' => null,
        ]);
    }

    public function test_no_permite_asignaciones_especificas_duplicadas(): void
    {
        $grado = GradoAcademico::factory()->create();
        $asignacion = AsignacionCurso::factory()->create(['grado_academico_id' => $grado]);

        $this->expectException(QueryException::class);

        AsignacionCurso::factory()->create([
            'curso_id' => $asignacion->curso_id,
            'colegio_id' => $asignacion->colegio_id,
            'grado_academico_id' => $grado,
        ]);
    }

    public function test_no_permite_finalizacion_anterior_al_inicio_de_asignacion(): void
    {
        $this->expectException(QueryException::class);

        AsignacionCurso::factory()->create([
            'inicia_en' => now(),
            'finaliza_en' => now()->subDay(),
        ]);
    }

    public function test_no_permite_progreso_duplicado_para_usuario_y_modulo(): void
    {
        $progreso = ProgresoModuloUsuario::factory()->create();

        $this->expectException(QueryException::class);

        ProgresoModuloUsuario::factory()->create([
            'usuario_id' => $progreso->usuario_id,
            'modulo_id' => $progreso->modulo_id,
        ]);
    }

    public function test_no_permite_numero_de_intento_cero(): void
    {
        $this->expectException(QueryException::class);

        IntentoActividad::factory()->create(['numero_intento' => 0]);
    }

    public function test_no_permite_roles_fuera_del_enum(): void
    {
        $this->expectException(QueryException::class);

        DB::table('usuarios')->insert([
            'nombre' => 'Usuario inválido',
            'correo_electronico' => 'invalido@example.test',
            'contrasena' => 'hash-no-real',
            'rol' => 'docente',
            'activo' => true,
        ]);
    }

    public function test_no_permite_eliminar_historial_mediante_cascada(): void
    {
        $progreso = ProgresoModuloUsuario::factory()->create();

        $this->expectException(QueryException::class);

        $progreso->modulo->delete();
    }
}
