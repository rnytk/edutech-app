<?php

namespace App\Filament\Resources\AsignacionesCursos;

use App\Filament\Resources\AsignacionesCursos\Pages\CrearAsignacionCurso;
use App\Filament\Resources\AsignacionesCursos\Pages\EditarAsignacionCurso;
use App\Filament\Resources\AsignacionesCursos\Pages\ListarAsignacionesCursos;
use App\Models\AsignacionCurso;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

class AsignacionCursoResource extends Resource
{
    protected static ?string $model = AsignacionCurso::class;

    protected static ?string $slug = 'asignaciones-cursos';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|\UnitEnum|null $navigationGroup = 'Asignaciones';

    protected static ?string $modelLabel = 'asignación de curso';

    protected static ?string $pluralModelLabel = 'asignaciones de cursos';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('curso_id')->label('Curso')->relationship('curso', 'titulo')->searchable()->preload()->required()->live(),
            Select::make('colegio_id')->label('Colegio')->relationship('colegio', 'nombre')->searchable()->preload()->required()->live(),
            Select::make('grado_academico_id')
                ->label('Grado académico')
                ->relationship('gradoAcademico', 'nombre', fn ($query) => $query->orderBy('orden'))
                ->placeholder('Todo el colegio')
                ->searchable()
                ->preload(),
            Toggle::make('activo')->label('Activa')->default(true),
            DateTimePicker::make('inicia_en')->label('Inicia en')->seconds(false),
            DateTimePicker::make('finaliza_en')
                ->label('Finaliza en')
                ->seconds(false)
                ->afterOrEqual('inicia_en')
                ->validationMessages([
                    'after_or_equal' => 'La fecha final debe ser posterior o igual a la fecha inicial.',
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('curso.titulo')->label('Curso')->searchable()->sortable(),
                TextColumn::make('colegio.nombre')->label('Colegio')->searchable()->sortable(),
                TextColumn::make('gradoAcademico.nombre')->label('Grado académico')->placeholder('Todo el colegio'),
                IconColumn::make('activo')->label('Activa')->boolean()->sortable(),
                TextColumn::make('inicia_en')->label('Inicio')->dateTime('d/m/Y H:i')->placeholder('Inmediato'),
                TextColumn::make('finaliza_en')->label('Fin')->dateTime('d/m/Y H:i')->placeholder('Sin vencimiento'),
            ])
            ->filters([
                SelectFilter::make('curso')->label('Curso')->relationship('curso', 'titulo'),
                SelectFilter::make('colegio')->label('Colegio')->relationship('colegio', 'nombre'),
                SelectFilter::make('gradoAcademico')->label('Grado académico')->relationship('gradoAcademico', 'nombre'),
            ])
            ->recordActions([EditAction::make()->label('Editar')]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListarAsignacionesCursos::route('/'),
            'create' => CrearAsignacionCurso::route('/create'),
            'edit' => EditarAsignacionCurso::route('/{record}/edit'),
        ];
    }

    /** @param array<string, mixed> $data */
    public static function validarUnicidad(array $data, ?AsignacionCurso $registro = null): void
    {
        $consulta = AsignacionCurso::query()
            ->where('curso_id', $data['curso_id'])
            ->where('colegio_id', $data['colegio_id'])
            ->when(
                filled($data['grado_academico_id'] ?? null),
                fn ($query) => $query->where('grado_academico_id', $data['grado_academico_id']),
                fn ($query) => $query->whereNull('grado_academico_id'),
            )
            ->when($registro, fn ($query) => $query->whereKeyNot($registro->getKey()));

        if ($consulta->exists()) {
            throw ValidationException::withMessages([
                'data.grado_academico_id' => 'Ya existe una asignación para el curso, colegio y grado seleccionados.',
            ]);
        }
    }
}
