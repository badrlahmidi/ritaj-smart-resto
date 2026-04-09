<?php

namespace App\Filament\Resources\IngredientResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StockMovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'stockMovements';

    protected static ?string $title = 'Historique des Mouvements de Stock';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reference')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'purchase' => 'success',
                        'sale'     => 'info',
                        'waste'    => 'danger',
                        'adjustment' => 'warning',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'purchase'   => 'Achat',
                        'sale'       => 'Vente',
                        'waste'      => 'Gaspillage',
                        'adjustment' => 'Ajustement',
                        default      => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Quantité')
                    ->formatStateUsing(fn ($state) => ($state > 0 ? '+' : '').$state)
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('cost')
                    ->label('Coût Unit.')
                    ->money('mad')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('reference')
                    ->label('Référence')
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'purchase'   => 'Achat',
                        'sale'       => 'Vente',
                        'waste'      => 'Gaspillage',
                        'adjustment' => 'Ajustement',
                    ])
                    ->label('Type de mouvement'),
            ])
            ->paginated([25, 50, 100]);
    }
}
