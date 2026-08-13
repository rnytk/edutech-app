<?php

namespace App\Filament\Resources\Colegios;

use App\Filament\Resources\Colegios\Pages\CrearColegio;
use App\Filament\Resources\Colegios\Pages\EditarColegio;
use App\Filament\Resources\Colegios\Pages\ListarColegios;
use App\Models\Colegio;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ColegioResource extends Resource
{
    protected static ?string $model = Colegio::class;

    protected static ?string $slug = 'colegios';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|\UnitEnum|null $navigationGroup = 'Instituciones';

    protected static ?string $modelLabel = 'colegio';

    protected static ?string $pluralModelLabel = 'colegios';

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nombre')
                ->label('Nombre')
                ->required()
                ->maxLength(255),
            TextInput::make('codigo')
                ->label('Código')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),
            Toggle::make('activo')
                ->label('Activo')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')->label('Nombre')->searchable()->sortable(),
                TextColumn::make('codigo')->label('Código')->searchable()->sortable(),
                IconColumn::make('activo')->label('Activo')->boolean()->sortable(),
                TextColumn::make('actualizado_en')->label('Actualizado')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('nombre')
            ->recordActions([
                EditAction::make()->label('Editar'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListarColegios::route('/'),
            'create' => CrearColegio::route('/create'),
            'edit' => EditarColegio::route('/{record}/edit'),
        ];
    }
}
