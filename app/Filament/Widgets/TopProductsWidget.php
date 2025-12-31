<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class TopProductsWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected static ?string $heading = 'Top 5 Produits (Mois en cours)';
    protected int | string | array $columnSpan = 'half';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                OrderItem::query()
                    ->select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(unit_price * quantity) as total_revenue'))
                    ->whereHas('order', fn($q) => $q->where('status', 'paid')->whereMonth('created_at', now()->month))
                    ->groupBy('product_id')
                    ->orderByDesc('total_qty')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produit')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('total_qty')
                    ->label('Ventes')
                    ->badge(),
                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('CA Généré')
                    ->money('mad')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
