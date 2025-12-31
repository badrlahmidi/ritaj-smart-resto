<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TableResource\Pages;
use App\Models\Table;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table as FilamentTable;

class TableResource extends Resource
{
    protected static ?string $model = Table::class;
    protected static ?string $navigationIcon = 'heroicon-o-table-cells';
    protected static ?string $navigationGroup = 'Infrastructure & Sécurité';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('area_id')
                    ->relationship('area', 'name')
                    ->required(),
                Forms\Components\TextInput::make('capacity')
                    ->numeric()
                    ->default(4)
                    ->required(),
                Forms\Components\Select::make('shape')
                    ->options([
                        'square' => 'Carrée',
                        'round' => 'Ronde',
                        'rectangle' => 'Rectangulaire',
                    ])
                    ->default('square')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'available' => 'Disponible',
                        'occupied' => 'Occupée',
                        'reserved' => 'Réservée',
                    ])
                    ->default('available')
                    ->required(),
                
                // Hidden fields for positioning (managed by floor plan editor ideally, but defaults needed here)
                Forms\Components\Hidden::make('position_x')->default(0),
                Forms\Components\Hidden::make('position_y')->default(0),
            ]);
    }

    public static function table(FilamentTable $table): FilamentTable
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('area.name')->sortable(),
                Tables\Columns\TextColumn::make('capacity')->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'occupied' => 'danger',
                        'reserved' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('area')->relationship('area', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'available' => 'Disponible',
                        'occupied' => 'Occupée',
                        'reserved' => 'Réservée',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListTables::route('/'),
            'create' => Pages\CreateTable::route('/create'),
            'edit' => Pages\EditTable::route('/{record}/edit'),
        ];
    }
}
