<?php

namespace App\Filament\Pages;

use App\Settings\GeneralSettings;
use App\Settings\PosSettings;
use App\Settings\PrinterSettings;
use App\Settings\FeatureSettings;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;

class ManageSettings extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?string $title = 'Paramètres Système';
    protected static ?int $navigationSort = 1;

    protected static string $settings = GeneralSettings::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Configuration')
                    ->tabs([
                        Tabs\Tab::make('Général')
                            ->icon('heroicon-o-building-storefront')
                            ->schema([
                                FileUpload::make('site_logo')
                                    ->label('Logo Restaurant')
                                    ->image()
                                    ->directory('logos'),
                                TextInput::make('site_name')
                                    ->label('Nom de l\'enseigne')
                                    ->required(),
                                Textarea::make('address')
                                    ->label('Adresse')
                                    ->rows(3),
                                TextInput::make('phone')
                                    ->label('Téléphone'),
                                Textarea::make('receipt_footer')
                                    ->label('Pied de page Ticket')
                                    ->placeholder('Merci de votre visite !'),
                            ]),
                        
                        Tabs\Tab::make('Règles POS')
                            ->icon('heroicon-o-currency-dollar')
                            ->schema([
                                Select::make('service_mode')
                                    ->label('Mode Service')
                                    ->options([
                                        'standard' => 'À Table (Paiement Fin)',
                                        'fast_food' => 'Rapide (Paiement Début)',
                                    ]),
                                Select::make('default_tax_rate')
                                    ->label('TVA par défaut')
                                    ->options([
                                        '0' => '0%',
                                        '10' => '10%',
                                        '20' => '20%'
                                    ]),
                                TextInput::make('currency')
                                    ->label('Devise')
                                    ->default('DH'),
                                Toggle::make('allow_negative_stock')
                                    ->label('Autoriser vente hors stock'),
                                Toggle::make('auto_clear_table')
                                    ->label('Libérer table après paiement'),
                            ]),

                        Tabs\Tab::make('Imprimantes')
                            ->icon('heroicon-o-printer')
                            ->schema([
                                Select::make('driver')
                                    ->label('Driver d\'Impression')
                                    ->options([
                                        'network' => 'Réseau (Ethernet/Wifi)',
                                        'windows' => 'Windows USB / Partage',
                                    ]),
                                TextInput::make('printer_ip_cashier')
                                    ->label('IP Caisse (Master)'),
                                TextInput::make('printer_ip_kitchen')
                                    ->label('IP Cuisine (KDS Backup)'),
                                TextInput::make('printer_ip_bar')
                                    ->label('IP Bar'),
                                Toggle::make('open_cash_drawer')
                                    ->label('Ouvrir Tiroir Caisse après impression'),
                            ]),

                        Tabs\Tab::make('Modules')
                            ->icon('heroicon-o-cpu-chip')
                            ->schema([
                                Toggle::make('enable_stock_module')
                                    ->label('Activer Module Stock'),
                                Toggle::make('enable_kds_module')
                                    ->label('Activer Écran Cuisine (KDS)'),
                                Toggle::make('enable_delivery_module')
                                    ->label('Activer Module Livraison'),
                                Toggle::make('enable_waiter_tablets')
                                    ->label('Activer Tablettes Serveurs'),
                            ]),
                    ])->columnSpanFull()
            ]);
    }
}
