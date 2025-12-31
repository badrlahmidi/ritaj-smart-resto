<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class SalesChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Ventes par Heure (Aujourd\'hui)';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        // Requires flowframe/laravel-trend package usually, 
        // but we can simulate standard query if package not present or install it.
        // Assuming we rely on standard Eloquent for simplicity in this demo.
        
        $data = Order::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->whereDate('created_at', now())
            ->where('status', 'paid')
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        // Fill missing hours
        $chartData = [];
        $labels = [];
        for ($i = 9; $i <= 23; $i++) { // From 9AM to 11PM
            $chartData[] = $data[$i] ?? 0;
            $labels[] = sprintf('%02d:00', $i);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Commandes',
                    'data' => $chartData,
                    'borderColor' => '#f59e0b', // Amber/Yellow
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
