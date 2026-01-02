<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class SalesByCategoryChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Ventes par Catégorie';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? now()->startOfMonth();
        $endDate = $this->filters['endDate'] ?? now()->endOfMonth();

        $data = OrderItem::query()
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_uuid', '=', 'orders.uuid')
            ->where('orders.status', 'paid')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select('categories.name', DB::raw('SUM(order_items.total_price) as total'))
            ->groupBy('categories.name')
            ->pluck('total', 'categories.name')
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'CA par Catégorie',
                    'data' => array_values($data),
                    'backgroundColor' => [
                        '#f59e0b', '#3b82f6', '#10b981', '#ef4444', '#8b5cf6',
                    ],
                ],
            ],
            'labels' => array_keys($data),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
