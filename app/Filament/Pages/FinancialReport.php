<?php

namespace App\Filament\Pages;

use App\Models\Order;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FinancialReport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationGroup = '📊 Rapports & Stats';
    protected static string $view = 'filament.pages.financial-report';
    protected static ?string $title = 'Rapport Financier';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'date_from' => now()->startOfMonth(),
            'date_to' => now()->endOfMonth(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('date_from')->label('Date Début')->required(),
                DatePicker::make('date_to')->label('Date Fin')->required(),
            ])
            ->columns(2)
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $dateFrom = $this->data['date_from'] ?? now()->startOfMonth();
                $dateTo = $this->data['date_to'] ?? now()->endOfMonth();

                return Order::query()
                    ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total_revenue, COUNT(*) as orders_count')
                    ->where('status', 'paid')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->groupBy('date')
                    ->orderByDesc('date');
            })
            ->columns([
                TextColumn::make('date')->date('d/m/Y')->label('Date'),
                TextColumn::make('orders_count')->label('Commandes'),
                TextColumn::make('total_revenue')->money('mad')->label('Chiffre d\'Affaires')->weight('bold'),
                TextColumn::make('tva_est')->label('TVA Estimée (10%)')
                    ->state(fn ($record) => number_format($record->total_revenue * 0.10, 2) . ' DH'),
            ]);
    }

    // Action pour mettre à jour le tableau quand on change les dates
    public function updateReport()
    {
        // Livewire refresh handled auto via reactive form or explicit button
    }
}
