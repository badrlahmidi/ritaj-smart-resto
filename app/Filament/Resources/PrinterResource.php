<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrinterResource\Pages;
use App\Models\Printer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PrinterResource extends Resource
{
    protected static ?string $model = Printer::class;
    protected static ?string $navigationIcon = 'heroicon-o-printer';
    protected static ?string $navigationGroup = 'Infrastructure & Sécurité'; // Updated Group

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->options([
                        'network' => 'Réseau (IP)',
                        'usb' => 'USB / Direct',
                    ])
                    ->required()
                    ->default('network'),
                Forms\Components\TextInput::make('path')
                    ->label('Adresse IP ou Chemin USB')
                    ->placeholder('192.168.1.200 ou /dev/usb/lp0')
                    ->nullable(),
                Forms\Components\TextInput::make('port')
                    ->numeric()
                    ->default(9100)
                    ->visible(fn (Forms\Get $get) => $get('type') === 'network'),
                
                // Fix: CheckboxList returns array, needs JSON casting in model
                Forms\Components\CheckboxList::make('station_tags')
                    ->label('Postes de cuisine associés')
                    ->options([
                        'bar' => 'Bar / Boissons',
                        'kitchen' => 'Cuisine Principale',
                        'grill' => 'Grillades',
                        'pizza' => 'Four à Pizza',
                    ])
                    ->columns(2),
                
                Forms\Components\Toggle::make('is_active')
                    ->label('Actif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'network' => 'info',
                        'usb' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('path')->label('Cible'),
                Tables\Columns\TextColumn::make('station_tags')
                    ->badge()
                    ->separator(',')
                    ->limitList(3),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                //
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
            'index' => Pages\ListPrinters::route('/'),
            'create' => Pages\CreatePrinter::route('/create'),
            'edit' => Pages\EditPrinter::route('/{record}/edit'),
        ];
    }
}
