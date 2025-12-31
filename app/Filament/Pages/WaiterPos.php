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
    protected static ?string $navigationGroup = 'Exploitation'; // Updated group
    protected static string $layout = 'filament-panels::components.layout.simple'; // Use simple layout (no sidebar)

    protected static string $view = 'filament.pages.waiter-pos';

    public ?string $heading = ''; // Remove default heading

    public function getMaxContentWidth(): \Filament\Support\Enums\MaxWidth | string | null
    {
        return 'full';
    }
}
