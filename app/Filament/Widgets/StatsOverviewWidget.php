<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Ingredient;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // CA Jour (Revenue Today)
        $revenueToday = Order::whereDate('created_at', $today)
            ->where('status', 'paid')
            ->sum('total_amount'); // Correction here: ensure column is total_amount
            
        $revenueYesterday = Order::whereDate('created_at', $yesterday)
            ->where('status', 'paid')
            ->sum('total_amount'); // Correction here: ensure column is total_amount

        $revenueChange = $revenueYesterday > 0 
            ? (($revenueToday - $revenueYesterday) / $revenueYesterday) * 100 
            : 0;

        // Ticket Moyen (Avg Ticket)
        $ordersCount = Order::whereDate('created_at', $today)->where('status', 'paid')->count();
        $avgTicket = $ordersCount > 0 ? $revenueToday / $ordersCount : 0;

        // Commandes Ouvertes (Open Orders)
        $openOrders = Order::whereIn('status', ['pending', 'in_progress', 'served'])->count();

        // Stock Alerts
        $lowStockIngredients = 0;
        
        try {
            if (Schema::hasTable('ingredients')) {
                 $lowStockIngredients = Ingredient::whereColumn('stock_quantity', '<=', 'alert_threshold')->count();
            }
        } catch (\Exception $e) {
            // Silently fail
        }

        return [
            Stat::make('Chiffre d\'Affaires (J)', number_format($revenueToday, 2) . ' DH')
                ->description($revenueChange >= 0 ? '+' . number_format($revenueChange, 1) . '% vs Hier' : number_format($revenueChange, 1) . '% vs Hier')
                ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueChange >= 0 ? 'success' : 'danger')
                ->chart([$revenueYesterday, $revenueToday]),

            Stat::make('Ticket Moyen', number_format($avgTicket, 2) . ' DH')
                ->description('Sur ' . $ordersCount . ' commandes')
                ->color('info'),

            Stat::make('Tables Actives', $openOrders)
                ->description('Commandes en cours')
                ->icon('heroicon-o-users')
                ->color('primary'),

            Stat::make('Alertes Stock', $lowStockIngredients)
                ->description('Ingrédients critiques')
                ->icon('heroicon-o-exclamation-triangle')
                ->color($lowStockIngredients > 0 ? 'danger' : 'success'),
        ];
    }
}
