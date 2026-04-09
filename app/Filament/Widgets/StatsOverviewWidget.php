<?php

namespace App\Filament\Widgets;

use App\Models\Ingredient;
use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = '30s';

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // CA Jour
        $revenueToday = Order::whereDate('created_at', $today)
            ->where('status', 'paid')
            ->sum('total_amount');

        $revenueYesterday = Order::whereDate('created_at', $yesterday)
            ->where('status', 'paid')
            ->sum('total_amount');

        $revenueChange = $revenueYesterday > 0
            ? (($revenueToday - $revenueYesterday) / $revenueYesterday) * 100
            : 0;

        // Hourly sparkline for today (9h-23h)
        $hourlyData = Order::selectRaw('HOUR(created_at) as hour, SUM(total_amount) as revenue')
            ->whereDate('created_at', $today)
            ->where('status', 'paid')
            ->groupBy('hour')
            ->pluck('revenue', 'hour')
            ->toArray();

        $sparkline = [];
        for ($h = 9; $h <= 23; $h++) {
            $sparkline[] = round($hourlyData[$h] ?? 0, 2);
        }

        // Ticket Moyen
        $ordersCount = Order::whereDate('created_at', $today)->where('status', 'paid')->count();
        $avgTicket = $ordersCount > 0 ? $revenueToday / $ordersCount : 0;

        // Commandes ouvertes
        $openOrders = Order::whereIn('status', [
            \App\Enums\OrderStatus::SentToKitchen->value,
            \App\Enums\OrderStatus::InService->value,
            \App\Enums\OrderStatus::PaymentPending->value,
            \App\Enums\OrderStatus::Draft->value,
        ])->count();

        // Alertes stock
        $lowStockIngredients = 0;
        try {
            if (Schema::hasTable('ingredients')) {
                $lowStockIngredients = Ingredient::whereColumn('stock_quantity', '<=', 'alert_threshold')->count();
            }
        } catch (\Exception) {
            // Silently fail
        }

        return [
            Stat::make('Chiffre d\'Affaires (J)', number_format($revenueToday, 2).' DH')
                ->description($revenueChange >= 0 ? '+'.number_format($revenueChange, 1).'% vs Hier' : number_format($revenueChange, 1).'% vs Hier')
                ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueChange >= 0 ? 'success' : 'danger')
                ->chart($sparkline),

            Stat::make('Ticket Moyen', number_format($avgTicket, 2).' DH')
                ->description('Sur '.$ordersCount.' commandes')
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
