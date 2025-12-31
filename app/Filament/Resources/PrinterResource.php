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
    protected static ?string $navigationGroup = '⚙️ Configuration';
    protected static ?string $navigationIcon = 'heroicon-o-printer';
    protected static ?string $label = 'Imprimante';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Connexion')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom (ex: Cuisine)')
                            ->required(),
                        Forms\Components\Select::make('type')
                            ->options([
                                'network' => 'Réseau (Ethernet/Wifi)',
                                'usb' => 'USB / Local',
                            ])
                            ->default('network')
                            ->reactive(),
                        Forms\Components\TextInput::make('ip_address')
                            ->label('Adresse IP')
                            ->placeholder('192.168.1.200')
                            ->hidden(fn (Forms\Get $get) => $get('type') !== 'network'),
                        Forms\Components\TextInput::make('port')
                            ->default(9100)
                            ->numeric()
                            ->hidden(fn (Forms\Get $get) => $get('type') !== 'network'),
                        Forms\Components\TextInput::make('path')
                            ->label('Chemin USB / Partage')
                            ->hidden(fn (Forms\Get $get) => $get('type') !== 'usb'),
                    ])->columns(2),

                Forms\Components\Section::make('Routage')
                    ->schema([
                        Forms\Components\CheckboxList::make('station_tags')
                            ->label('Postes assignés')
                            ->options([
                                'kitchen' => 'Cuisine Principale',
                                'pizza_oven' => 'Four Pizza',
                                'bar' => 'Bar',
                            ])
                            ->columns(3),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->weight('bold'),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('ip_address')->label('IP / Port')
                    ->description(fn (Printer $record) => $record->type === 'network' ? $record->port : $record->path),
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
            'index' => Pages\ListPrinters::route('/'),
            'create' => Pages\CreatePrinter::route('/create'),
            'edit' => Pages\EditPrinter::route('/{record}/edit'),
        ];
    }
}
