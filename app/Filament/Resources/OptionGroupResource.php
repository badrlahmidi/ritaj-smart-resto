<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OptionGroupResource\Pages;
use App\Models\OptionGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OptionGroupResource extends Resource
{
    protected static ?string $model = OptionGroup::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';
    protected static ?string $navigationGroup = '📦 Catalogue';
    protected static ?int $navigationSort = 3;
    protected static ?string $label = 'Groupe d\'Options';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Configuration')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom du Groupe')
                            ->placeholder('Ex: Cuisson Viande, Sauces Pizza')
                            ->required(),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Toggle::make('is_multiselect')
                                    ->label('Choix Multiple')
                                    ->reactive(),
                                
                                Forms\Components\Toggle::make('is_required')
                                    ->label('Obligatoire')
                                    ->helperText('Le client DOIT choisir une option'),

                                Forms\Components\TextInput::make('max_options')
                                    ->label('Max Sélection')
                                    ->numeric()
                                    ->minValue(1)
                                    ->hidden(fn (Forms\Get $get) => !$get('is_multiselect')),
                            ]),
                    ]),

                Forms\Components\Section::make('Liste des Options')
                    ->schema([
                        Forms\Components\Repeater::make('options')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Libellé')
                                    ->required()
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('price_modifier')
                                    ->label('Surcoût (DH)')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('+')
                                    ->columnSpan(1),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->addActionLabel('Ajouter une option'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('options_count')
                    ->counts('options')
                    ->label('Nb Options')
                    ->badge(),
                Tables\Columns\IconColumn::make('is_required')
                    ->label('Requis')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_multiselect')
                    ->label('Multi')
                    ->boolean(),
            ])
            ->filters([
                //
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOptionGroups::route('/'),
            'create' => Pages\CreateOptionGroup::route('/create'),
            'edit' => Pages\EditOptionGroup::route('/{record}/edit'),
        ];
    }
}
