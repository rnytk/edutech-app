<?php

namespace App\Models;

use App\Enums\RolUsuario;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable implements FilamentUser, HasName
{
    use HasFactory, Notifiable;

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    protected $table = 'usuarios';

    protected $guarded = [];

    protected $hidden = [
        'contrasena',
        'token_recordatorio',
    ];

    protected function casts(): array
    {
        return [
            'rol' => RolUsuario::class,
            'activo' => 'boolean',
            'creado_en' => 'immutable_datetime',
            'actualizado_en' => 'immutable_datetime',
        ];
    }

    public function getAuthPasswordName(): string
    {
        return 'contrasena';
    }

    public function getRememberTokenName(): string
    {
        return 'token_recordatorio';
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin'
            && $this->activo
            && $this->rol === RolUsuario::Superadministrador;
    }

    public function getFilamentName(): string
    {
        return $this->nombre;
    }

    public function colegio(): BelongsTo
    {
        return $this->belongsTo(Colegio::class, 'colegio_id');
    }

    public function gradoAcademico(): BelongsTo
    {
        return $this->belongsTo(GradoAcademico::class, 'grado_academico_id');
    }

    public function intentosActividades(): HasMany
    {
        return $this->hasMany(IntentoActividad::class, 'usuario_id');
    }

    public function progresosModulos(): HasMany
    {
        return $this->hasMany(ProgresoModuloUsuario::class, 'usuario_id');
    }
}
