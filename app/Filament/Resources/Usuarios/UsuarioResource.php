<?php

namespace App\Filament\Resources\Usuarios;

use App\Enums\RolUsuario;
use App\Filament\Resources\Usuarios\Pages\CrearUsuario;
use App\Filament\Resources\Usuarios\Pages\EditarUsuario;
use App\Filament\Resources\Usuarios\Pages\ListarUsuarios;
use App\Models\Usuario;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UsuarioResource extends Resource
{
    protected static ?string $model = Usuario::class;

    protected static ?string $slug = 'usuarios';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|\UnitEnum|null $navigationGroup = 'Instituciones';

    protected static ?string $modelLabel = 'usuario';

    protected static ?string $pluralModelLabel = 'usuarios';

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nombre')->label('Nombre')->required()->maxLength(255),
            TextInput::make('correo_electronico')
                ->label('Correo electrónico')
                ->email()
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),
            Select::make('rol')
                ->label('Rol')
                ->options([
                    RolUsuario::Superadministrador->value => 'Superadministrador',
                    RolUsuario::Estudiante->value => 'Estudiante',
                ])
                ->required()
                ->live()
                ->afterStateUpdated(static function (?string $state, Set $set): void {
                    if ($state === RolUsuario::Superadministrador->value) {
                        $set('colegio_id', null);
                        $set('grado_academico_id', null);
                    }
                }),
            Toggle::make('activo')->label('Activo')->default(true),
            Select::make('colegio_id')
                ->label('Colegio')
                ->relationship('colegio', 'nombre', fn ($query) => $query->orderBy('nombre'))
                ->searchable()
                ->preload()
                ->required(fn (Get $get): bool => $get('rol') === RolUsuario::Estudiante->value)
                ->visible(fn (Get $get): bool => $get('rol') === RolUsuario::Estudiante->value),
            Select::make('grado_academico_id')
                ->label('Grado académico')
                ->relationship('gradoAcademico', 'nombre', fn ($query) => $query->orderBy('orden'))
                ->searchable()
                ->preload()
                ->required(fn (Get $get): bool => $get('rol') === RolUsuario::Estudiante->value)
                ->visible(fn (Get $get): bool => $get('rol') === RolUsuario::Estudiante->value),
            TextInput::make('contrasena')
                ->label('Contraseña')
                ->password()
                ->revealable()
                ->autocomplete('new-password')
                ->required(fn (string $operation): bool => $operation === 'create')
                ->minLength(8)
                ->formatStateUsing(static fn (): null => null)
                ->dehydrated(fn (?string $state): bool => filled($state))
                ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                ->helperText('Déjala vacía al editar para conservar la contraseña actual.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')->label('Nombre')->searchable()->sortable(),
                TextColumn::make('correo_electronico')->label('Correo electrónico')->searchable()->sortable(),
                TextColumn::make('rol')->label('Rol')->badge()->formatStateUsing(
                    fn (RolUsuario|string $state): string => ($state instanceof RolUsuario ? $state : RolUsuario::from($state)) === RolUsuario::Superadministrador
                        ? 'Superadministrador'
                        : 'Estudiante'
                ),
                TextColumn::make('colegio.nombre')->label('Colegio')->placeholder('No aplica'),
                TextColumn::make('gradoAcademico.nombre')->label('Grado académico')->placeholder('No aplica'),
                IconColumn::make('activo')->label('Activo')->boolean()->sortable(),
            ])
            ->filters([
                SelectFilter::make('rol')->label('Rol')->options([
                    RolUsuario::Superadministrador->value => 'Superadministrador',
                    RolUsuario::Estudiante->value => 'Estudiante',
                ]),
                SelectFilter::make('colegio')->label('Colegio')->relationship('colegio', 'nombre'),
            ])
            ->defaultSort('nombre')
            ->recordActions([EditAction::make()->label('Editar')]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListarUsuarios::route('/'),
            'create' => CrearUsuario::route('/create'),
            'edit' => EditarUsuario::route('/{record}/edit'),
        ];
    }
}
