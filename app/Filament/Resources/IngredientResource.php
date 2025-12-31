<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IngredientResource\Pages;
use App\Models\Ingredient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class IngredientResource extends Resource
{
    protected static ?string $model = Ingredient::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?string $navigationGroup = '📦 Catalogue & Stock'; // Changed
    protected static ?int $navigationSort = 2; // After Products
    protected static ?string $label = 'Ingrédient / Stock';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom de l\'ingrédient')
                            ->required(),
                        
                        Forms\Components\Select::make('unit')
                            ->label('Unité de Stock')
                            ->options([
                                'kg' => 'Kilogramme (kg)',
                                'g' => 'Gramme (g)',
                                'l' => 'Litre (L)',
                                'ml' => 'Millilitre (ml)',
                                'unit' => 'Unité (pcs)',
                            ])
                            ->required()
                            ->default('kg'),

                        Forms\Components\TextInput::make('cost_per_unit')
                            ->label('Coût par unité (DH)')
                            ->numeric()
                            ->default(0)
                            ->prefix('DH'),

                        Forms\Components\TextInput::make('stock_quantity')
                            ->label('Stock Actuel')
                            ->numeric()
                            ->default(0),

                        Forms\Components\TextInput::make('alert_threshold')
                            ->label('Seuil d\'alerte')
                            ->numeric()
                            ->default(1),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->suffix(fn (Ingredient $record) => ' ' . $record->unit)
                    ->color(fn (Ingredient $record) => $record->stock_quantity <= $record->alert_threshold ? 'danger' : 'success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cost_per_unit')
                    ->label('Coût Unitaire')
                    ->money('mad'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('low_stock')
                    ->query(fn ($query) => $query->whereColumn('stock_quantity', '<=', 'alert_threshold'))
                    ->label('Stock Critique'),
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
            'index' => Pages\ListIngredients::route('/'),
            'create' => Pages\CreateIngredient::route('/create'),
            'edit' => Pages\EditIngredient::route('/{record}/edit'),
        ];
    }
}
