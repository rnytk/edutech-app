<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsignacionCurso extends Model
{
    use HasFactory;

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    protected $table = 'asignaciones_cursos';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'inicia_en' => 'immutable_datetime',
            'finaliza_en' => 'immutable_datetime',
            'creado_en' => 'immutable_datetime',
            'actualizado_en' => 'immutable_datetime',
        ];
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    public function colegio(): BelongsTo
    {
        return $this->belongsTo(Colegio::class, 'colegio_id');
    }

    public function gradoAcademico(): BelongsTo
    {
        return $this->belongsTo(GradoAcademico::class, 'grado_academico_id');
    }
}
