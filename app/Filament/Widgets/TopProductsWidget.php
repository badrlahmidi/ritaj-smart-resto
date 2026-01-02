<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class TopProductsWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Top 5 Produits';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $startDate = $this->filters['startDate'] ?? now()->startOfMonth();
                $endDate = $this->filters['endDate'] ?? now()->endOfMonth();

                return OrderItem::query()
                    ->join('orders', 'order_items.order_uuid', '=', 'orders.uuid')
                    ->join('products', 'order_items.product_id', '=', 'products.id')
                    ->where('orders.status', 'paid')
                    ->whereBetween('orders.created_at', [$startDate, $endDate])
                    ->select(
                        'products.name',
                        'products.image_url',
                        DB::raw('SUM(order_items.quantity) as total_qty'),
                        DB::raw('SUM(order_items.total_price) as total_revenue')
                    )
                    ->groupBy('products.id', 'products.name', 'products.image_url')
                    ->orderByDesc('total_revenue')
                    ->limit(5);
            })
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')->circular()->label(''),
                Tables\Columns\TextColumn::make('name')->label('Produit')->weight('bold'),
                Tables\Columns\TextColumn::make('total_qty')->label('Qté Vendue'),
                Tables\Columns\TextColumn::make('total_revenue')->money('mad')->label('CA Généré'),
            ])
            ->paginated(false);
    }
}