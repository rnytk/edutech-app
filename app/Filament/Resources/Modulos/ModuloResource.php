<?php

namespace App\Filament\Resources\Modulos;

use App\Filament\Resources\Modulos\Pages\CrearModulo;
use App\Filament\Resources\Modulos\Pages\EditarModulo;
use App\Filament\Resources\Modulos\Pages\ListarModulos;
use App\Filament\Resources\Modulos\RelationManagers\CapsulasRelationManager;
use App\Models\Modulo;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ModuloResource extends Resource
{
    protected static ?string $model = Modulo::class;

    protected static ?string $slug = 'modulos';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedPuzzlePiece;

    protected static string|\UnitEnum|null $navigationGroup = 'Contenido educativo';

    protected static ?string $modelLabel = 'módulo';

    protected static ?string $pluralModelLabel = 'módulos';

    protected static ?string $recordTitleAttribute = 'titulo';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('nivel_id')
                ->label('Nivel')
                ->relationship('nivel', 'titulo', fn ($query) => $query->with('curso')->orderBy('orden'))
                ->getOptionLabelFromRecordUsing(fn ($record): string => "{$record->curso->titulo} — {$record->titulo}")
                ->searchable(['titulo'])
                ->preload()
                ->required(),
            TextInput::make('titulo')->label('Título')->required()->maxLength(255),
            RichEditor::make('descripcion')->label('Descripción')->columnSpanFull(),
            FileUpload::make('ruta_imagen')
                ->label('Imagen')
                ->image()
                ->disk('public')
                ->directory('modulos')
                ->visibility('public'),
            TextInput::make('orden')->label('Orden')->required()->integer()->minValue(0)->default(0),
            Toggle::make('publicado')->label('Publicado')->default(false),
            Builder::make('bloques_contenido')
                ->label('Bloques de contenido')
                ->addActionLabel('Agregar tarjeta')
                ->minItems(1)
                ->required()
                ->blocks([
                    Block::make('tarjeta')
                        ->label('Tarjeta')
                        ->icon(Heroicon::OutlinedRectangleStack)
                        ->schema([
                            Hidden::make('uuid')->default(fn (): string => (string) Str::uuid())->uuid()->required(),
                            TextInput::make('titulo')->label('Título')->required()->maxLength(255),
                            FileUpload::make('ruta_imagen')
                                ->label('Imagen')
                                ->image()
                                ->disk('public')
                                ->directory('modulos/tarjetas')
                                ->visibility('public'),
                            RichEditor::make('contenido')->label('Contenido')->required()->columnSpanFull(),
                        ]),
                ])
                ->columnSpanFull(),
            Builder::make('actividades')
                ->label('Actividades')
                ->addActionLabel('Agregar actividad')
                ->minItems(1)
                ->required()
                ->blocks(self::bloquesActividades())
                ->columnSpanFull(),
            RichEditor::make('mensaje_cierre')->label('Mensaje de cierre')->columnSpanFull(),
        ]);
    }

    /** @return array<int, Block> */
    private static function bloquesActividades(): array
    {
        return [
            Block::make('falso_verdadero')
                ->label('Falso o verdadero')
                ->schema([
                    self::campoUuid(),
                    TextInput::make('pregunta')->label('Pregunta')->required()->maxLength(1000),
                    Toggle::make('respuesta_correcta')->label('La respuesta correcta es verdadero')->default(false),
                ]),
            Block::make('opcion_multiple')
                ->label('Opción múltiple')
                ->schema([
                    self::campoUuid(),
                    TextInput::make('pregunta')->label('Pregunta')->required()->maxLength(1000)->columnSpanFull(),
                    Repeater::make('opciones')
                        ->label('Opciones')
                        ->minItems(2)
                        ->required()
                        ->schema([
                            self::campoUuid(),
                            TextInput::make('texto')->label('Texto')->required()->maxLength(1000),
                        ])
                        ->columns(1)
                        ->columnSpanFull(),
                    Select::make('opcion_correcta_uuid')
                        ->label('Opción correcta')
                        ->options(static fn (Get $get): array => collect($get('opciones') ?? [])
                            ->filter(fn (mixed $opcion): bool => is_array($opcion) && filled($opcion['uuid'] ?? null))
                            ->mapWithKeys(fn (array $opcion): array => [
                                $opcion['uuid'] => filled($opcion['texto'] ?? null) ? $opcion['texto'] : 'Opción sin texto',
                            ])
                            ->all())
                        ->required(),
                ]),
            Block::make('respuesta_directa')
                ->label('Respuesta directa')
                ->schema([
                    self::campoUuid(),
                    TextInput::make('pregunta')->label('Pregunta')->required()->maxLength(1000),
                ]),
            Block::make('ordenacion')
                ->label('Ordenación')
                ->schema([
                    self::campoUuid(),
                    TextInput::make('instruccion')->label('Instrucción')->required()->maxLength(1000)->columnSpanFull(),
                    Repeater::make('elementos')
                        ->label('Elementos en el orden correcto')
                        ->minItems(2)
                        ->required()
                        ->schema([
                            self::campoUuid(),
                            TextInput::make('texto')->label('Texto')->required()->maxLength(1000),
                            TextInput::make('posicion')->label('Posición')->required()->integer()->minValue(1)->distinct(),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),
                ]),
            Block::make('clasificacion')
                ->label('Clasificación')
                ->schema([
                    self::campoUuid(),
                    TextInput::make('instruccion')->label('Instrucción')->required()->maxLength(1000)->columnSpanFull(),
                    Repeater::make('categorias')
                        ->label('Categorías')
                        ->minItems(2)
                        ->required()
                        ->schema([
                            self::campoUuid(),
                            TextInput::make('nombre')->label('Nombre de la categoría')->required()->maxLength(255),
                            Repeater::make('elementos')
                                ->label('Elementos')
                                ->minItems(1)
                                ->required()
                                ->schema([
                                    self::campoUuid(),
                                    TextInput::make('texto')->label('Texto')->required()->maxLength(1000),
                                ])
                                ->columnSpanFull(),
                        ])
                        ->columnSpanFull(),
                ]),
        ];
    }

    private static function campoUuid(): Hidden
    {
        return Hidden::make('uuid')->default(fn (): string => (string) Str::uuid())->uuid()->required();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nivel.curso.titulo')->label('Curso')->searchable(),
                TextColumn::make('nivel.titulo')->label('Nivel')->searchable()->sortable(),
                TextColumn::make('titulo')->label('Módulo')->searchable()->sortable(),
                TextColumn::make('orden')->label('Orden')->sortable(),
                IconColumn::make('publicado')->label('Publicado')->boolean()->sortable(),
                TextColumn::make('capsulas_count')->label('Cápsulas')->counts('capsulas'),
            ])
            ->defaultSort('orden')
            ->recordActions([EditAction::make()->label('Editar')]);
    }

    public static function getRelations(): array
    {
        return [CapsulasRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListarModulos::route('/'),
            'create' => CrearModulo::route('/create'),
            'edit' => EditarModulo::route('/{record}/edit'),
        ];
    }
}
