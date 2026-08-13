<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cursos', function (Blueprint $tabla) {
            $tabla->string('titulo_bienvenida')->nullable()->after('ruta_imagen');
            $tabla->text('contenido_bienvenida')->nullable()->after('titulo_bienvenida');
        });
    }

    public function down(): void
    {
        Schema::table('cursos', function (Blueprint $tabla) {
            $tabla->dropColumn(['titulo_bienvenida', 'contenido_bienvenida']);
        });
    }
};
