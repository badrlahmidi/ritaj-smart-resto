<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Pages\Dashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Analytics extends Dashboard
{
    use HasFiltersForm;

    protected static ?string $title = 'Analyses & Statistiques';
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationGroup = '📊 Rapports & Stats';
    protected static ?int $navigationSort = 1;

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        DatePicker::make('startDate')
                            ->label('Du')
                            ->default(now()->startOfMonth()),
                        DatePicker::make('endDate')
                            ->label('Au')
                            ->default(now()->endOfMonth()),
                    ])
                    ->columns(2),
            ]);
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\AnalyticsStatsOverview::class,
            \App\Filament\Widgets\SalesByCategoryChart::class,
            \App\Filament\Widgets\TopProductsWidget::class,
        ];
    }
}
