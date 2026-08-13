<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('niveles', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->foreignId('curso_id')->constrained('cursos')->restrictOnDelete();
            $tabla->string('titulo');
            $tabla->text('descripcion')->nullable();
            $tabla->string('ruta_imagen')->nullable();
            $tabla->integer('orden')->default(0);
            $tabla->boolean('publicado')->default(false);
            $tabla->timestampTz('creado_en')->nullable();
            $tabla->timestampTz('actualizado_en')->nullable();

            $tabla->index(['curso_id', 'publicado', 'orden'], 'niveles_curso_publicado_orden_indice');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('niveles');
    }
};
