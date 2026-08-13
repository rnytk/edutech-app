<?php

namespace App\Filament\Resources\GradosAcademicos;

use App\Filament\Resources\GradosAcademicos\Pages\CrearGradoAcademico;
use App\Filament\Resources\GradosAcademicos\Pages\EditarGradoAcademico;
use App\Filament\Resources\GradosAcademicos\Pages\ListarGradosAcademicos;
use App\Models\GradoAcademico;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GradoAcademicoResource extends Resource
{
    protected static ?string $model = GradoAcademico::class;

    protected static ?string $slug = 'grados-academicos';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string|\UnitEnum|null $navigationGroup = 'Instituciones';

    protected static ?string $modelLabel = 'grado académico';

    protected static ?string $pluralModelLabel = 'grados académicos';

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nombre')->label('Nombre')->required()->maxLength(255),
            TextInput::make('codigo')->label('Código')->required()->maxLength(255)->unique(ignoreRecord: true),
            TextInput::make('orden')->label('Orden')->required()->integer()->minValue(0)->default(0),
            Toggle::make('activo')->label('Activo')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')->label('Nombre')->searchable()->sortable(),
                TextColumn::make('codigo')->label('Código')->searchable()->sortable(),
                TextColumn::make('orden')->label('Orden')->sortable(),
                IconColumn::make('activo')->label('Activo')->boolean()->sortable(),
            ])
            ->defaultSort('orden')
            ->recordActions([EditAction::make()->label('Editar')]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListarGradosAcademicos::route('/'),
            'create' => CrearGradoAcademico::route('/create'),
            'edit' => EditarGradoAcademico::route('/{record}/edit'),
        ];
    }
}
