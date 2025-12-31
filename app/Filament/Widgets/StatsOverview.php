<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    // Rafraîchir toutes les 30s
    protected int | string | array $pollingInterval = '30s';

    protected function getStats(): array
    {
        // CA du jour
        $dailyRevenue = Order::whereDate('created_at', today())
            ->where('status', 'paid')
            ->sum('total_amount');

        // Commandes en attente (Cuisine)
        $pendingOrders = Order::where('status', 'sent_to_kitchen')->count();

        // Total commandes aujourd'hui
        $totalOrders = Order::whereDate('created_at', today())->count();

        return [
            Stat::make('Chiffre d\'affaires (Jour)', number_format($dailyRevenue, 2) . ' DH')
                ->description('Total encaissé aujourd\'hui')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 17]), // Dummy chart

            Stat::make('Commandes en Cuisine', $pendingOrders)
                ->description('Tickets à préparer')
                ->descriptionIcon('heroicon-m-fire')
                ->color($pendingOrders > 5 ? 'danger' : 'warning'),

            Stat::make('Tickets Total (Jour)', $totalOrders)
                ->description('Nombre de tables servies')
                ->descriptionIcon('heroicon-m-ticket'),
        ];
    }
}
