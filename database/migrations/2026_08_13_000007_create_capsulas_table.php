<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('capsulas', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->foreignId('modulo_id')->constrained('modulos')->restrictOnDelete();
            $tabla->string('titulo')->nullable();
            $tabla->text('contenido');
            $tabla->string('ruta_imagen')->nullable();
            $tabla->integer('orden')->default(0);
            $tabla->boolean('activo')->default(true);
            $tabla->timestampTz('creado_en')->nullable();
            $tabla->timestampTz('actualizado_en')->nullable();

            $tabla->index(['modulo_id', 'activo', 'orden'], 'capsulas_modulo_activo_orden_indice');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capsulas');
    }
};
