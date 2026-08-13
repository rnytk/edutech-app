<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->string('nombre');
            $tabla->string('correo_electronico')->unique('usuarios_correo_electronico_unico');
            $tabla->string('contrasena');
            $tabla->string('rol', 32);
            $tabla->foreignId('colegio_id')->nullable()->constrained('colegios')->restrictOnDelete();
            $tabla->foreignId('grado_academico_id')->nullable()->constrained('grados_academicos')->restrictOnDelete();
            $tabla->boolean('activo')->default(true);
            $tabla->string('token_recordatorio', 100)->nullable();
            $tabla->timestampTz('creado_en')->nullable();
            $tabla->timestampTz('actualizado_en')->nullable();

            $tabla->index(['colegio_id', 'grado_academico_id', 'activo'], 'usuarios_colegio_grado_activo_indice');
        });

        DB::statement(<<<'SQL'
            ALTER TABLE usuarios
            ADD CONSTRAINT usuarios_rol_valido
            CHECK (rol IN ('superadministrador', 'estudiante'))
        SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
