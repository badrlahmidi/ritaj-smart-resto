<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationGroup = '⚙️ Configuration';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $label = 'Utilisateur';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations Personnelles')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => \Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create'),
                        Forms\Components\FileUpload::make('avatar_url')
                            ->avatar(),
                    ])->columns(2),

                Forms\Components\Section::make('Sécurité & Accès')
                    ->schema([
                        Forms\Components\Select::make('role')
                            ->options([
                                'admin' => 'Administrateur (Tout)',
                                'manager' => 'Gérant (POS + Stock)',
                                'server' => 'Serveur (POS)',
                                'kitchen' => 'Cuisinier (KDS)',
                            ])
                            ->required()
                            ->default('server'),
                        Forms\Components\TextInput::make('pin_code')
                            ->label('Code PIN (POS)')
                            ->numeric()
                            ->length(4)
                            ->password()
                            ->revealable()
                            ->placeholder('1234'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar_url')->circular(),
                Tables\Columns\TextColumn::make('name')->weight('bold')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'manager' => 'warning',
                        'server' => 'info',
                        'kitchen' => 'gray',
                    }),
                Tables\Columns\TextColumn::make('pin_code')->label('PIN')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
