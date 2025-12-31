<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;
    protected static ?string $navigationGroup = '📚 Menu & Produits';
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $label = 'Catégorie';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Détails Catégorie')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('image')
                            ->image()
                            ->directory('categories'),
                        Forms\Components\ColorPicker::make('color')
                            ->label('Couleur d\'affichage (POS)'),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ])->columns(2),
                
                Forms\Components\Section::make('Routage Impression')
                    ->schema([
                        Forms\Components\Select::make('printer_id')
                            ->relationship('printer', 'name')
                            ->label('Envoyer vers Imprimante')
                            ->placeholder('Sélectionner une imprimante (ex: Cuisine)')
                            ->helperText('Les produits de cette catégorie seront imprimés sur ce périphérique.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\ColorColumn::make('color'),
                Tables\Columns\TextColumn::make('printer.name')
                    ->label('Imprimante')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
