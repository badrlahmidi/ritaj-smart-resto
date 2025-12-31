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

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = '⚙️ Config';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Identité & Rôle')
                            ->columns(2)
                            ->schema([
                                Forms\Components\FileUpload::make('avatar_url')
                                    ->label('Photo Profil')
                                    ->image()
                                    ->avatar()
                                    ->imageEditor()
                                    ->circleCropper()
                                    ->directory('avatars')
                                    ->columnSpanFull(),
                                    
                                Forms\Components\TextInput::make('name')
                                    ->label('Nom Complet')
                                    ->required()
                                    ->maxLength(255),
                                    
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                                    
                                Forms\Components\Select::make('role')
                                    ->label('Rôle Principal')
                                    ->options([
                                        'super_admin' => 'Super Admin',
                                        'manager' => 'Manager',
                                        'waiter' => 'Serveur',
                                        'kitchen' => 'Cuisinier',
                                        'cashier' => 'Caissier',
                                    ])
                                    ->required(),
                                    
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Compte Actif')
                                    ->default(true)
                                    ->columnSpanFull(),
                            ]),
                    ])->columnSpan(2),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Sécurité (Back-Office)')
                            ->schema([
                                Forms\Components\TextInput::make('password')
                                    ->label('Mot de passe')
                                    ->password()
                                    ->dehydrateStateUsing(fn ($state) => \Illuminate\Support\Facades\Hash::make($state))
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->required(fn (string $context): bool => $context === 'create')
                                    ->maxLength(255),
                            ]),

                        Forms\Components\Section::make('Sécurité POS (Tablette)')
                            ->schema([
                                Forms\Components\TextInput::make('new_pin')
                                    ->label('Code PIN (4 chiffres)')
                                    ->numeric()
                                    ->length(4)
                                    ->password()
                                    ->mask('9999')
                                    ->placeholder('1234')
                                    ->helperText('Ce code déverrouille l\'écran de caisse/serveur.')
                                    ->dehydrated(false) // Handle manually
                                    ->afterStateHydrated(function ($component) {
                                        $component->state('');
                                    }),
                            ]),
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar_url')
                    ->label('')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->icon('heroicon-m-envelope')
                    ->color('gray'),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'manager' => 'primary',
                        'waiter' => 'info',
                        'kitchen' => 'warning',
                        'cashier' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('pin')
                    ->label('PIN')
                    ->boolean()
                    ->state(fn (User $record) => $record->pin()->exists())
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->color(fn (string $state) => $state ? 'success' : 'danger'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'super_admin' => 'Super Admin',
                        'manager' => 'Manager',
                        'waiter' => 'Serveur',
                        'kitchen' => 'Cuisinier',
                        'cashier' => 'Caissier',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
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
