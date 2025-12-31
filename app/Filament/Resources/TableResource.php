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
    protected static ?string $navigationGroup = '⚙️ Config'; // Changed
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('capacity')
                    ->numeric()
                    ->default(4),
                Forms\Components\Select::make('status')
                    ->options([
                        'available' => 'Available',
                        'occupied' => 'Occupied',
                        'reserved' => 'Reserved',
                    ])
                    ->default('available')
                    ->required(),
            ]);
    }

    public static function table(FilamentTableInstance $table): FilamentTableInstance
    {
        return $table
            ->columns([
                FilamentTable\Columns\TextColumn::make('name')->searchable(),
                FilamentTable\Columns\TextColumn::make('capacity'),
                FilamentTable\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'occupied' => 'danger',
                        'reserved' => 'warning',
                    }),
            ])
            ->filters([
                //
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
