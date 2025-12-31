<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TableResource\Pages;
use App\Models\Table;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables as FilamentTable;
use Filament\Tables\Table as FilamentTableInstance;

class TableResource extends Resource
{
    protected static ?string $model = Table::class;

    protected static ?string $navigationIcon = 'heroicon-o-view-columns';
    protected static ?string $navigationGroup = '⚙️ Config'; 
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Détails Table')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Numéro / Nom')
                                    ->required()
                                    ->placeholder('T1, VIP...'),
                                
                                Forms\Components\Select::make('area_id')
                                    ->label('Zone')
                                    ->relationship('area', 'name')
                                    ->required()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')->required(),
                                    ]),

                                Forms\Components\TextInput::make('capacity')
                                    ->label('Couverts')
                                    ->numeric()
                                    ->default(4)
                                    ->minValue(1),

                                Forms\Components\Select::make('shape')
                                    ->label('Forme')
                                    ->options([
                                        'square' => 'Carrée (2-4p)',
                                        'round' => 'Ronde (4-8p)',
                                        'rectangle' => 'Rectangulaire (6+)',
                                    ])
                                    ->default('square')
                                    ->required(),
                            ])->columns(2),
                    ]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Position (Plan)')
                            ->description('Coordonnées sur le plan virtuel')
                            ->schema([
                                Forms\Components\TextInput::make('position_x')
                                    ->label('X (%)')
                                    ->numeric(),
                                Forms\Components\TextInput::make('position_y')
                                    ->label('Y (%)')
                                    ->numeric(),
                                Forms\Components\Select::make('status')
                                    ->options([
                                        'available' => 'Libre',
                                        'occupied' => 'Occupée',
                                        'reserved' => 'Réservée',
                                    ])
                                    ->default('available'),
                            ])->columns(2),
                    ]),
            ]);
    }

    public static function table(FilamentTableInstance $table): FilamentTableInstance
    {
        return $table
            ->columns([
                FilamentTable\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                FilamentTable\Columns\TextColumn::make('area.name')
                    ->label('Zone')
                    ->sortable()
                    ->badge(),
                FilamentTable\Columns\TextColumn::make('capacity')
                    ->label('Pers.')
                    ->icon('heroicon-o-user'),
                FilamentTable\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'occupied' => 'danger',
                        'reserved' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->filters([
                FilamentTable\Filters\SelectFilter::make('area_id')
                    ->relationship('area', 'name')
                    ->label('Zone'),
            ])
            ->actions([
                FilamentTable\Actions\EditAction::make(),
            ])
            ->bulkActions([
                FilamentTable\Actions\BulkActionGroup::make([
                    FilamentTable\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListTables::route('/'),
            'create' => Pages\CreateTable::route('/create'),
            'edit' => Pages\EditTable::route('/{record}/edit'),
        ];
    }
}
