<?php

namespace App\Filament\Pages;

use App\Models\GeneralSetting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = '⚙️ Configuration';
    protected static ?string $title = 'Général & Impression';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = GeneralSetting::current();
        $this->form->fill($settings->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Settings')
                    ->tabs([
                        // TAB A: IDENTITY
                        Tabs\Tab::make('Identité Restaurant')
                            ->icon('heroicon-o-building-storefront')
                            ->schema([
                                TextInput::make('restaurant_name')
                                    ->label('Nom du Restaurant')
                                    ->required(),
                                FileUpload::make('logo_path')
                                    ->label('Logo')
                                    ->image()
                                    ->directory('settings'),
                                TextInput::make('phone')
                                    ->tel(),
                                TextInput::make('email')
                                    ->email(),
                                Textarea::make('address')
                                    ->rows(3),
                            ]),

                        // TAB B: RECEIPT
                        Tabs\Tab::make('Personnalisation Ticket')
                            ->icon('heroicon-o-receipt-percent')
                            ->schema([
                                Textarea::make('receipt_header')
                                    ->label('En-tête Ticket')
                                    ->placeholder('Bienvenue chez Ritaj Smart Resto'),
                                Textarea::make('receipt_footer')
                                    ->label('Pied de page Ticket')
                                    ->placeholder('Merci de votre visite !'),
                                Toggle::make('show_wifi')
                                    ->label('Afficher Wifi sur Ticket')
                                    ->reactive(),
                                TextInput::make('wifi_ssid')
                                    ->label('Nom Wifi')
                                    ->hidden(fn ($get) => !$get('show_wifi')),
                                TextInput::make('wifi_password')
                                    ->label('Mot de passe Wifi')
                                    ->hidden(fn ($get) => !$get('show_wifi')),
                                Toggle::make('show_server_name'),
                                Toggle::make('show_tva_breakdown')
                                    ->label('Détail TVA'),
                                TextInput::make('qr_code_link')
                                    ->label('Lien QR Code (Menu/Insta)'),
                            ]),

                        // TAB C: WORKFLOW
                        Tabs\Tab::make('Règles de Gestion')
                            ->icon('heroicon-o-scale')
                            ->schema([
                                Select::make('service_mode')
                                    ->options([
                                        'standard' => 'Standard (Commande -> Paiement)',
                                        'fast_food' => 'Fast Food (Paiement -> Commande)',
                                    ]),
                                Toggle::make('table_closure_auto')
                                    ->label('Clôture table auto après paiement')
                                    ->default(true),
                                Toggle::make('stock_block_sale')
                                    ->label('Bloquer vente si stock épuisé'),
                                TextInput::make('default_vat_rate')
                                    ->label('Taux TVA par défaut (%)')
                                    ->numeric()
                                    ->suffix('%'),
                            ]),

                        // TAB D: MODULES
                        Tabs\Tab::make('Modules & Flags')
                            ->icon('heroicon-o-cpu-chip')
                            ->schema([
                                Toggle::make('module_kds')
                                    ->label('Activer Écran Cuisine (KDS)'),
                                Toggle::make('module_delivery')
                                    ->label('Activer Module Livraison'),
                                Toggle::make('module_smart_cash')
                                    ->label('Activer Smart Cash (Maroc)'),
                            ]),
                    ])->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $settings = GeneralSetting::current();
        $settings->update($this->form->getState());

        Notification::make()
            ->title('Paramètres sauvegardés avec succès')
            ->success()
            ->send();
    }
}
