<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    // ... existing navigation config ...
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = '📦 Catalogue';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        // ... existing form ...
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Informations Produit')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                
                                Forms\Components\Textarea::make('short_description')
                                    ->label('Description courte (App)')
                                    ->rows(2)
                                    ->maxLength(255),

                                Forms\Components\Select::make('category_id')
                                    ->relationship('category', 'name')
                                    ->required()
                                    ->preload()
                                    ->searchable(),
                                
                                Forms\Components\RichEditor::make('description')
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Section::make('Variantes & Options')
                            ->schema([
                                Forms\Components\Select::make('optionGroups')
                                    ->label('Groupes d\'Options')
                                    ->relationship('optionGroups', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->helperText('Ex: Cuisson, Sauces, Suppléments...'),
                            ]),
                    ])->columnSpan(2),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Prix & Stock')
                            ->schema([
                                Forms\Components\TextInput::make('price')
                                    ->required()
                                    ->numeric()
                                    ->prefix('DH'),
                                Forms\Components\TextInput::make('cost')
                                    ->numeric()
                                    ->prefix('DH')
                                    ->label('Coût de revient'),
                                Forms\Components\Toggle::make('has_stock')
                                    ->label('Gestion de stock')
                                    ->reactive(),
                                Forms\Components\TextInput::make('stock_quantity')
                                    ->numeric()
                                    ->hidden(fn (Forms\Get $get) => !$get('has_stock')),
                            ]),
                        
                        Forms\Components\Section::make('Configuration Cuisine')
                            ->schema([
                                Forms\Components\Select::make('kitchen_station')
                                    ->label('Poste de préparation')
                                    ->options([
                                        'kitchen' => 'Cuisine Principale',
                                        'pizza_oven' => 'Four à Pizza',
                                        'bar' => 'Bar / Boissons',
                                        'dessert' => 'Pâtisserie',
                                    ])
                                    ->default('kitchen')
                                    ->required(),
                            ]),

                        Forms\Components\Section::make('Visuel')
                            ->schema([
                                Forms\Components\FileUpload::make('image_url')
                                    ->image()
                                    ->directory('products'),
                                Forms\Components\Toggle::make('is_available')
                                    ->default(true),
                            ]),
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')->circular(),
                Tables\Columns\TextColumn::make('name')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('category.name')->badge(),
                Tables\Columns\TextColumn::make('price')->money('mad')->sortable(),
                Tables\Columns\TextColumn::make('cost')->money('mad')->label('Coût')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->badge()
                    ->color(fn (Product $record): string => match (true) {
                        !$record->has_stock => 'gray',
                        $record->stock_quantity <= 5 => 'danger',
                        $record->stock_quantity <= 10 => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\TextColumn::make('kitchen_station')
                    ->label('Poste')
                    ->badge()
                    ->color('info'),
                Tables\Columns\IconColumn::make('is_available')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')->relationship('category', 'name'),
                Tables\Filters\SelectFilter::make('kitchen_station')
                    ->options([
                        'kitchen' => 'Cuisine',
                        'bar' => 'Bar',
                        'pizza_oven' => 'Pizza',
                    ]),
                Tables\Filters\Filter::make('low_stock')
                    ->label('Stock Critique (< 10)')
                    ->query(fn (Builder $query): Builder => $query->where('has_stock', true)->where('stock_quantity', '<=', 10)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make(),
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
