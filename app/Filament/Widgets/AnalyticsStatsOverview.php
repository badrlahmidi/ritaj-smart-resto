<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class AnalyticsStatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? now()->startOfMonth();
        $endDate = $this->filters['endDate'] ?? now()->endOfMonth();

        // 1. Revenue & Orders
        $data = Order::query()
            ->where('status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('SUM(total_amount) as revenue, COUNT(*) as count')
            ->first();

        $revenue = $data->revenue ?? 0;
        $count = $data->count ?? 1; // Avoid division by zero

        // 2. Average Basket
        $aov = $revenue / ($data->count > 0 ? $data->count : 1);

        // 3. Comparison (Previous Period)
        // Simple simplified comparison logic
        $previousRevenue = Order::query()
            ->where('status', 'paid')
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->subMonth(),
                Carbon::parse($endDate)->subMonth()
            ])
            ->sum('total_amount');

        $growth = $previousRevenue > 0 
            ? (($revenue - $previousRevenue) / $previousRevenue) * 100 
            : 0;

        return [
            Stat::make('Chiffre d\'Affaires', number_format($revenue, 2) . ' DH')
                ->description($growth > 0 ? '+' . number_format($growth, 1) . '% vs M-1' : number_format($growth, 1) . '% vs M-1')
                ->descriptionIcon($growth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart([$previousRevenue, $revenue])
                ->color($growth >= 0 ? 'success' : 'danger'),

            Stat::make('Commandes', $data->count ?? 0)
                ->description('Sur la période')
                ->color('info'),

            Stat::make('Panier Moyen', number_format($aov, 2) . ' DH')
                ->description('Moyenne par client')
                ->color('warning'),
        ];
    }
}
