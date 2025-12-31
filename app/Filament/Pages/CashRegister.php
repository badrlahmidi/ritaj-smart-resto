<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class CashRegister extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Caisse';
    protected static ?string $title = 'Caisse Enregistreuse';
    protected static ?string $slug = 'cash-register';
    protected static ?string $navigationGroup = 'Opérations';

    protected static string $view = 'filament.pages.cash-register';

    public function getMaxContentWidth(): \Filament\Support\Enums\MaxWidth | string | null
    {
        return 'full';
    }
}
