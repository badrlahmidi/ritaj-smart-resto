<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cake';
    protected static ?string $navigationGroup = 'Menu';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Détails Produit')
                            ->schema([
                                Forms\Components\Select::make('category_id')
                                    ->relationship('category', 'name')
                                    ->required(),
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('price')
                                    ->required()
                                    ->numeric()
                                    ->prefix('DH'),
                                Forms\Components\FileUpload::make('image_url')
                                    ->label('Photo')
                                    ->image()
                                    ->imageEditor()
                                    ->directory('products'),
                                Forms\Components\Toggle::make('is_available')
                                    ->required()
                                    ->default(true),
                            ])->columns(2),

                        // Section Recette / Ingrédients
                        Forms\Components\Section::make('Fiche Technique (Recette)')
                            ->description('Ajoutez les ingrédients nécessaires à la fabrication de ce produit')
                            ->schema([
                                Forms\Components\Repeater::make('ingredients')
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\Select::make('ingredient_id')
                                            ->relationship('ingredient', 'name')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->distinct()
                                            ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                        Forms\Components\TextInput::make('quantity')
                                            ->label('Quantité')
                                            ->numeric()
                                            ->required(),
                                        Forms\Components\Select::make('unit')
                                            ->label('Unité')
                                            ->options([
                                                'kg' => 'Kilogramme (kg)',
                                                'g' => 'Gramme (g)',
                                                'l' => 'Litre (L)',
                                                'ml' => 'Millilitre (ml)',
                                                'unit' => 'Unité (pcs)',
                                            ])
                                            ->default('kg')
                                            ->required(),
                                    ])
                                    ->columns(3)
                                    ->defaultItems(0)
                                    ->addActionLabel('Ajouter un ingrédient')
                            ])
                            ->collapsed(),
                    ])->columnSpan(2),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Stock (Produit Fini)')
                            ->schema([
                                Forms\Components\Toggle::make('track_stock')
                                    ->label('Suivi de stock')
                                    ->helperText('Activer uniquement si vous gérez le stock du produit fini (ex: Canette). Pour les plats cuisinés, utilisez la fiche technique.')
                                    ->reactive(),
                                Forms\Components\TextInput::make('stock_quantity')
                                    ->label('Quantité en stock')
                                    ->numeric()
                                    ->default(0)
                                    ->hidden(fn (Forms\Get $get) => !$get('track_stock')),
                                Forms\Components\TextInput::make('alert_threshold')
                                    ->label('Seuil d\'alerte')
                                    ->numeric()
                                    ->default(5)
                                    ->hidden(fn (Forms\Get $get) => !$get('track_stock')),
                            ]),
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Photo')
                    ->square(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('mad')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_available')
                    ->boolean(),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->color(fn (Product $record) => $record->track_stock && $record->stock_quantity <= $record->alert_threshold ? 'danger' : 'success')
                    ->formatStateUsing(fn (Product $record) => $record->track_stock ? $record->stock_quantity : '-')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_available'),
                Tables\Filters\Filter::make('low_stock')
                    ->query(fn ($query) => $query->where('track_stock', true)->whereColumn('stock_quantity', '<=', 'alert_threshold'))
                    ->label('Stock Faible'),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
