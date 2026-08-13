<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Modulo extends Model
{
    use HasFactory;

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    protected $table = 'modulos';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'orden' => 'integer',
            'bloques_contenido' => 'array',
            'actividades' => 'array',
            'publicado' => 'boolean',
            'creado_en' => 'immutable_datetime',
            'actualizado_en' => 'immutable_datetime',
        ];
    }

    public function scopePublicados(Builder $consulta): Builder
    {
        return $consulta->where('publicado', true);
    }

    public function nivel(): BelongsTo
    {
        return $this->belongsTo(Nivel::class, 'nivel_id');
    }

    public function capsulas(): HasMany
    {
        return $this->hasMany(Capsula::class, 'modulo_id');
    }

    public function intentosActividades(): HasMany
    {
        return $this->hasMany(IntentoActividad::class, 'modulo_id');
    }

    public function progresosUsuarios(): HasMany
    {
        return $this->hasMany(ProgresoModuloUsuario::class, 'modulo_id');
    }
}
