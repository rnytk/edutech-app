<?php

namespace App\Filament\Resources\IntentosActividades;

use App\Enums\TipoActividad;
use App\Filament\Resources\IntentosActividades\Pages\ListarIntentosActividades;
use App\Filament\Resources\IntentosActividades\Pages\VerIntentoActividad;
use App\Models\Colegio;
use App\Models\Curso;
use App\Models\IntentoActividad;
use App\Models\Nivel;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class IntentoActividadResource extends Resource
{
    protected static ?string $model = IntentoActividad::class;

    protected static ?string $slug = 'intentos-actividades';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentMagnifyingGlass;

    protected static string|\UnitEnum|null $navigationGroup = 'Seguimiento';

    protected static ?string $modelLabel = 'intento de actividad';

    protected static ?string $pluralModelLabel = 'intentos de actividades';

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('usuario.nombre')->label('Estudiante'),
            TextEntry::make('usuario.colegio.nombre')->label('Colegio'),
            TextEntry::make('modulo.nivel.curso.titulo')->label('Curso'),
            TextEntry::make('modulo.nivel.titulo')->label('Nivel'),
            TextEntry::make('modulo.titulo')->label('Módulo'),
            TextEntry::make('tipo_actividad')->label('Tipo de actividad')->formatStateUsing(self::etiquetarTipo(...)),
            TextEntry::make('actividad_uuid')->label('UUID de actividad')->copyable(),
            TextEntry::make('numero_intento')->label('Número de intento'),
            TextEntry::make('respuesta')
                ->label('Respuesta enviada')
                ->formatStateUsing(fn (mixed $state): string => json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '{}')
                ->columnSpanFull(),
            TextEntry::make('correcta')->label('Resultado')->formatStateUsing(
                fn (?bool $state): string => match ($state) {
                    true => 'Correcta',
                    false => 'Incorrecta',
                    null => 'Sin calificación automática',
                }
            ),
            TextEntry::make('respondido_en')->label('Respondido en')->dateTime('d/m/Y H:i:s'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'usuario.colegio',
                'modulo.nivel.curso',
            ]))
            ->columns([
                TextColumn::make('usuario.nombre')->label('Estudiante')->searchable()->sortable(),
                TextColumn::make('usuario.colegio.nombre')->label('Colegio')->searchable(),
                TextColumn::make('modulo.nivel.curso.titulo')->label('Curso')->searchable(),
                TextColumn::make('modulo.nivel.titulo')->label('Nivel')->searchable(),
                TextColumn::make('modulo.titulo')->label('Módulo')->searchable(),
                TextColumn::make('tipo_actividad')->label('Tipo')->badge()->formatStateUsing(self::etiquetarTipo(...)),
                TextColumn::make('numero_intento')->label('Intento')->sortable(),
                IconColumn::make('correcta')->label('Correcta')->boolean(),
                TextColumn::make('respondido_en')->label('Respondido')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('colegio_id')
                    ->label('Colegio')
                    ->options(fn (): array => Colegio::query()->orderBy('nombre')->pluck('nombre', 'id')->all())
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'] ?? null,
                        fn (Builder $query, mixed $valor): Builder => $query->whereHas(
                            'usuario',
                            fn (Builder $usuario): Builder => $usuario->where('colegio_id', $valor),
                        ),
                    )),
                SelectFilter::make('usuario_id')->label('Estudiante')->relationship('usuario', 'nombre')->searchable()->preload(),
                SelectFilter::make('curso_id')
                    ->label('Curso')
                    ->options(fn (): array => Curso::query()->orderBy('titulo')->pluck('titulo', 'id')->all())
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'] ?? null,
                        fn (Builder $query, mixed $valor): Builder => $query->whereHas(
                            'modulo.nivel',
                            fn (Builder $nivel): Builder => $nivel->where('curso_id', $valor),
                        ),
                    )),
                SelectFilter::make('nivel_id')
                    ->label('Nivel')
                    ->options(fn (): array => Nivel::query()->orderBy('titulo')->pluck('titulo', 'id')->all())
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'] ?? null,
                        fn (Builder $query, mixed $valor): Builder => $query->whereHas(
                            'modulo',
                            fn (Builder $modulo): Builder => $modulo->where('nivel_id', $valor),
                        ),
                    )),
                SelectFilter::make('modulo_id')->label('Módulo')->relationship('modulo', 'titulo')->searchable()->preload(),
                SelectFilter::make('tipo_actividad')->label('Tipo de actividad')->options(self::opcionesTipos()),
            ])
            ->defaultSort('respondido_en', 'desc')
            ->recordActions([ViewAction::make()->label('Ver')]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListarIntentosActividades::route('/'),
            'view' => VerIntentoActividad::route('/{record}'),
        ];
    }

    /** @return array<string, string> */
    private static function opcionesTipos(): array
    {
        return [
            TipoActividad::FalsoVerdadero->value => 'Falso o verdadero',
            TipoActividad::OpcionMultiple->value => 'Opción múltiple',
            TipoActividad::RespuestaDirecta->value => 'Respuesta directa',
            TipoActividad::Ordenacion->value => 'Ordenación',
            TipoActividad::Clasificacion->value => 'Clasificación',
        ];
    }

    private static function etiquetarTipo(string $state): string
    {
        return self::opcionesTipos()[$state] ?? $state;
    }
}
