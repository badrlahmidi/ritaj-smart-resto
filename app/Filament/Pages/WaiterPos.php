<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Panel;

class WaiterPos extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';
    protected static ?string $navigationLabel = 'Mode Serveur (POS)';
    protected static ?string $title = 'Prise de commande';
    protected static ?string $slug = 'pos';
    protected static ?string $navigationGroup = 'Opérations';

    protected static string $view = 'filament.pages.waiter-pos';

    // Masquer le header par défaut pour gagner de la place sur mobile
    public ?string $heading = '';
    
    // Layout pleine largeur
    public function getMaxContentWidth(): \Filament\Support\Enums\MaxWidth | string | null
    {
        return 'full';
    }
}
