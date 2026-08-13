<?php

namespace App\Filament\Resources\Niveles;

use App\Filament\Resources\Niveles\Pages\CrearNivel;
use App\Filament\Resources\Niveles\Pages\EditarNivel;
use App\Filament\Resources\Niveles\Pages\ListarNiveles;
use App\Models\Nivel;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NivelResource extends Resource
{
    protected static ?string $model = Nivel::class;

    protected static ?string $slug = 'niveles';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Contenido educativo';

    protected static ?string $modelLabel = 'nivel';

    protected static ?string $pluralModelLabel = 'niveles';

    protected static ?string $recordTitleAttribute = 'titulo';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('curso_id')
                ->label('Curso')
                ->relationship('curso', 'titulo')
                ->searchable()
                ->preload()
                ->required(),
            TextInput::make('titulo')->label('Título')->required()->maxLength(255),
            RichEditor::make('descripcion')->label('Descripción')->columnSpanFull(),
            FileUpload::make('ruta_imagen')
                ->label('Imagen')
                ->image()
                ->disk('public')
                ->directory('niveles')
                ->visibility('public'),
            TextInput::make('orden')->label('Orden')->required()->integer()->minValue(0)->default(0),
            Toggle::make('publicado')->label('Publicado')->default(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('curso.titulo')->label('Curso')->searchable()->sortable(),
                TextColumn::make('titulo')->label('Título')->searchable()->sortable(),
                TextColumn::make('orden')->label('Orden')->sortable(),
                IconColumn::make('publicado')->label('Publicado')->boolean()->sortable(),
            ])
            ->defaultSort('orden')
            ->recordActions([EditAction::make()->label('Editar')]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListarNiveles::route('/'),
            'create' => CrearNivel::route('/create'),
            'edit' => EditarNivel::route('/{record}/edit'),
        ];
    }
}
