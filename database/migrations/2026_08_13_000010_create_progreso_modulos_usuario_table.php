<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progreso_modulos_usuario', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->foreignId('usuario_id')->constrained('usuarios')->restrictOnDelete();
            $tabla->foreignId('modulo_id')->constrained('modulos')->restrictOnDelete();
            $tabla->timestampTz('completado_en');
            $tabla->timestampTz('creado_en')->nullable();
            $tabla->timestampTz('actualizado_en')->nullable();

            $tabla->unique(['usuario_id', 'modulo_id'], 'progreso_usuario_modulo_unico');
            $tabla->index('modulo_id', 'progreso_modulo_indice');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progreso_modulos_usuario');
    }
};
