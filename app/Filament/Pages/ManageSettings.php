<?php

namespace App\Filament\Pages;

use App\Settings\GeneralSettings;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;

class ManageSettings extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = '⚙️ Configuration';
    protected static ?string $title = 'Paramètres Généraux';
    protected static ?int $navigationSort = 10;

    protected static string $settings = GeneralSettings::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Settings')
                    ->tabs([
                        // ONOLET 1: IDENTITÉ
                        Tabs\Tab::make('🏢 Identité & Contact')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('site_name')
                                            ->label('Nom de l\'établissement')
                                            ->required()
                                            ->columnSpan(1),
                                        TextInput::make('email')
                                            ->label('Email de contact')
                                            ->email()
                                            ->columnSpan(1),
                                        FileUpload::make('site_logo')
                                            ->label('Logo Principal')
                                            ->image()
                                            ->directory('settings')
                                            ->avatar()
                                            ->columnSpan(2),
                                        TextInput::make('phone')
                                            ->label('Téléphone')
                                            ->tel()
                                            ->prefixIcon('heroicon-m-phone'),
                                        Textarea::make('address')
                                            ->label('Adresse Complète')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ]),
                                Section::make('Réseaux Sociaux')
                                    ->schema([
                                        TextInput::make('facebook_url')
                                            ->label('Page Facebook')
                                            ->url()
                                            ->prefix('https://facebook.com/'),
                                        TextInput::make('instagram_url')
                                            ->label('Compte Instagram')
                                            ->prefix('@'),
                                    ])->columns(2)->collapsed(),
                            ]),

                        // ONGLET 2: OPÉRATIONS
                        Tabs\Tab::make('⚙️ Opérations & Finance')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('currency_symbol')
                                            ->label('Symbole Monétaire')
                                            ->default('DH')
                                            ->placeholder('DH, €, $'),
                                        TextInput::make('default_tax_rate')
                                            ->label('Taux de TVA par défaut (%)')
                                            ->numeric()
                                            ->suffix('%')
                                            ->default(10),
                                    ]),
                            ]),

                        // ONGLET 3: MODULES
                        Tabs\Tab::make('🚀 Fonctionnalités')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Section::make('Logistique')
                                            ->schema([
                                                Toggle::make('enable_stock_management')
                                                    ->label('Gestion des Stocks')
                                                    ->helperText('Suivi des ingrédients et inventaire.')
                                                    ->onIcon('heroicon-m-check')
                                                    ->offIcon('heroicon-m-x-mark')
                                                    ->onColor('success'),
                                                Toggle::make('enable_kds')
                                                    ->label('Écran Cuisine (KDS)')
                                                    ->helperText('Envoi des commandes vers les écrans en cuisine.')
                                                    ->onColor('warning'),
                                            ])->columnSpan(1),

                                        Section::make('Types de Commande')
                                            ->schema([
                                                Toggle::make('enable_takeaway')
                                                    ->label('Vente à Emporter')
                                                    ->default(true),
                                                Toggle::make('enable_delivery')
                                                    ->label('Livraison')
                                                    ->default(true),
                                            ])->columnSpan(1),
                                    ]),
                            ]),

                        // ONGLET 4: IMPRESSION
                        Tabs\Tab::make('🧾 Impression')
                            ->schema([
                                Textarea::make('receipt_footer')
                                    ->label('Pied de page du ticket')
                                    ->rows(3)
                                    ->helperText('Message de remerciement, infos légales...'),
                                
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('wifi_ssid')
                                            ->label('Nom du Wifi (SSID)')
                                            ->prefixIcon('heroicon-m-wifi'),
                                        TextInput::make('wifi_password')
                                            ->label('Mot de passe Wifi'),
                                    ]),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
}