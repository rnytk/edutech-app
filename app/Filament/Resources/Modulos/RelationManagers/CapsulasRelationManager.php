<?php

namespace App\Filament\Resources\Modulos\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CapsulasRelationManager extends RelationManager
{
    protected static string $relationship = 'capsulas';

    protected static ?string $title = 'Cápsulas';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('titulo')->label('Título')->maxLength(255),
            TextInput::make('orden')->label('Orden')->required()->integer()->minValue(0)->default(0),
            RichEditor::make('contenido')->label('Contenido')->required()->columnSpanFull(),
            FileUpload::make('ruta_imagen')
                ->label('Imagen')
                ->image()
                ->disk('public')
                ->directory('modulos/capsulas')
                ->visibility('public'),
            Toggle::make('activo')->label('Activa')->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modelLabel('cápsula')
            ->pluralModelLabel('cápsulas')
            ->columns([
                TextColumn::make('titulo')->label('Título')->placeholder('Sin título')->searchable(),
                TextColumn::make('orden')->label('Orden')->sortable(),
                IconColumn::make('activo')->label('Activa')->boolean()->sortable(),
            ])
            ->defaultSort('orden')
            ->headerActions([CreateAction::make()->label('Crear cápsula')])
            ->recordActions([EditAction::make()->label('Editar')]);
    }
}
