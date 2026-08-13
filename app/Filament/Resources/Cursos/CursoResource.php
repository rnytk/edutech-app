<?php

namespace App\Filament\Resources\Cursos;

use App\Filament\Resources\Cursos\Pages\CrearCurso;
use App\Filament\Resources\Cursos\Pages\EditarCurso;
use App\Filament\Resources\Cursos\Pages\ListarCursos;
use App\Models\Curso;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CursoResource extends Resource
{
    protected static ?string $model = Curso::class;

    protected static ?string $slug = 'cursos';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|\UnitEnum|null $navigationGroup = 'Contenido educativo';

    protected static ?string $modelLabel = 'curso';

    protected static ?string $pluralModelLabel = 'cursos';

    protected static ?string $recordTitleAttribute = 'titulo';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('titulo')->label('Título')->required()->maxLength(255),
            TextInput::make('orden')->label('Orden')->required()->integer()->minValue(0)->default(0),
            RichEditor::make('descripcion')->label('Descripción')->columnSpanFull(),
            FileUpload::make('ruta_imagen')
                ->label('Imagen')
                ->image()
                ->disk('public')
                ->directory('cursos')
                ->visibility('public'),
            TextInput::make('titulo_bienvenida')
                ->label('Título de bienvenida')
                ->maxLength(255)
                ->columnSpanFull(),
            RichEditor::make('contenido_bienvenida')
                ->label('Contenido de bienvenida')
                ->columnSpanFull(),
            Toggle::make('publicado')->label('Publicado')->default(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('titulo')->label('Título')->searchable()->sortable(),
                TextColumn::make('orden')->label('Orden')->sortable(),
                IconColumn::make('publicado')->label('Publicado')->boolean()->sortable(),
                TextColumn::make('niveles_count')->label('Niveles')->counts('niveles'),
            ])
            ->defaultSort('orden')
            ->recordActions([EditAction::make()->label('Editar')]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListarCursos::route('/'),
            'create' => CrearCurso::route('/create'),
            'edit' => EditarCurso::route('/{record}/edit'),
        ];
    }
}
