<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;

class KitchenDisplay extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-fire';
    protected static ?string $navigationLabel = 'Écran Cuisine (KDS)';
    protected static ?string $title = 'Commandes en Cuisine';
    protected static ?string $slug = 'kitchen';
    protected static ?string $navigationGroup = 'Opérations';

    protected static string $view = 'filament.pages.kitchen-display';

    public function getMaxContentWidth(): \Filament\Support\Enums\MaxWidth | string | null
    {
        return 'full';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Actualiser')
                ->action('$refresh'),
        ];
    }
}
