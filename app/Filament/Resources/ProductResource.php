<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cake';
    protected static ?string $navigationGroup = '📦 Catalogue & Stock';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3) // Layout 3 colonnes : 2/3 Main + 1/3 Sidebar
            ->schema([
                // --- COLONNE PRINCIPALE (2/3) ---
                Forms\Components\Group::make()
                    ->columnSpan(['lg' => 2])
                    ->schema([
                        Forms\Components\Section::make('Informations Générales')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nom du Produit')
                                    ->required()
                                    ->maxLength(255),
                                
                                Forms\Components\Select::make('category_id')
                                    ->relationship('category', 'name')
                                    ->label('Catégorie')
                                    ->required()
                                    ->preload()
                                    ->searchable(),

                                Forms\Components\Textarea::make('description')
                                    ->label('Description courte')
                                    ->rows(2)
                                    ->placeholder('Ingrédients principaux, allergènes...'),
                            ])->columns(2),

                        Forms\Components\Section::make('Stratégie Tarifaire')
                            ->description('Définissez les prix selon le canal de vente.')
                            ->columns(3)
                            ->schema([
                                Forms\Components\TextInput::make('price')
                                    ->label('Prix À Table (Base)')
                                    ->numeric()
                                    ->prefix('DH')
                                    ->minValue(0)
                                    ->required()
                                    ->live(onBlur: true),

                                Forms\Components\TextInput::make('price_takeaway')
                                    ->label('Prix Emporter')
                                    ->numeric()
                                    ->prefix('DH')
                                    ->minValue(0)
                                    ->placeholder(fn (Get $get) => $get('price') ? $get('price') . ' (Auto)' : 'Idem Base'),

                                Forms\Components\TextInput::make('price_delivery')
                                    ->label('Prix Livraison')
                                    ->numeric()
                                    ->prefix('DH')
                                    ->minValue(0)
                                    ->placeholder(fn (Get $get) => $get('price') ? $get('price') . ' (Auto)' : 'Idem Base'),
                            ]),

                        Forms\Components\Section::make('Fiche Technique (Recette)')
                            ->description('Ajoutez les ingrédients consommés à chaque vente.')
                            ->schema([
                                Forms\Components\Repeater::make('ingredients')
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\Select::make('ingredient_id')
                                            ->relationship('ingredient', 'name')
                                            ->label('Ingrédient')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                            
                                        Forms\Components\TextInput::make('quantity')
                                            ->label('Qté')
                                            ->numeric()
                                            ->default(1)
                                            ->required(),
                                            
                                        Forms\Components\Select::make('unit')
                                            ->label('Unité')
                                            ->options([
                                                'kg' => 'kg', 'g' => 'g', 'l' => 'L', 'ml' => 'ml', 'unit' => 'pcs'
                                            ])
                                            ->default('kg')
                                            ->required(),
                                    ])
                                    ->columns(3)
                                    ->defaultItems(0)
                                    ->addActionLabel('Ajouter ingrédient')
                            ])
                            ->collapsed(),
                    ]),

                // --- COLONNE LATÉRALE (1/3) ---
                Forms\Components\Group::make()
                    ->columnSpan(['lg' => 1])
                    ->schema([
                        Forms\Components\Section::make('Média')
                            ->schema([
                                Forms\Components\FileUpload::make('image_url')
                                    ->label('Photo')
                                    ->image()
                                    ->imageEditor()
                                    ->directory('products')
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Section::make('Paramètres & Visibilité')
                            ->schema([
                                Forms\Components\Toggle::make('is_available')
                                    ->label('Disponible à la vente')
                                    ->helperText('Affiché sur le menu serveur')
                                    ->onColor('success')
                                    ->default(true),

                                Forms\Components\Toggle::make('track_stock')
                                    ->label('Gérer Stock (Produit Fini)')
                                    ->helperText('Pour canettes, desserts tout faits...')
                                    ->reactive(),

                                Forms\Components\TextInput::make('stock_quantity')
                                    ->label('Stock Actuel')
                                    ->numeric()
                                    ->default(0)
                                    ->hidden(fn (Get $get) => !$get('track_stock')),
                                
                                Forms\Components\TextInput::make('alert_threshold')
                                    ->label('Seuil Alerte')
                                    ->numeric()
                                    ->default(5)
                                    ->hidden(fn (Get $get) => !$get('track_stock')),

                                Forms\Components\Select::make('printer_destination')
                                    ->label('Imprimante Cible') // Champ virtuel pour l'instant (à implémenter en DB plus tard si besoin)
                                    ->options([
                                        'kitchen' => 'Cuisine',
                                        'bar' => 'Bar',
                                        'pizza' => 'Four Pizza'
                                    ])
                                    ->default('kitchen')
                                    ->selectablePlaceholder(false),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Photo')
                    ->square()
                    ->size(40),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('Désignation')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                    
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Catégorie')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('price')
                    ->label('Prix')
                    ->money('mad')
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('is_available') // Toggle direct dans la liste
                    ->label('Dispo.'),

                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->badge()
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
