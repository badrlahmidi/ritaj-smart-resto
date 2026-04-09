<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\Payment;
use App\Settings\GeneralSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class DailyClosure extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';
    protected static ?string $navigationLabel = 'Clôture Journalière';
    protected static ?string $title = 'Clôture de Caisse';
    protected static ?string $navigationGroup = 'Pilotage & Finance';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.daily-closure';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'closure_date' => now()->toDateString(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('closure_date')
                    ->label('Date de Clôture')
                    ->required()
                    ->default(now()->toDateString()),
            ])
            ->statePath('data');
    }

    public function getClosureData(): array
    {
        $date = $this->data['closure_date'] ?? now()->toDateString();

        // Paid orders on that date
        $orders = Order::whereDate('created_at', $date)
            ->where('status', 'paid')
            ->with('payments')
            ->get();

        $totalRevenue = $orders->sum('total_amount');
        $ordersCount = $orders->count();
        $avgTicket = $ordersCount > 0 ? $totalRevenue / $ordersCount : 0;

        // By payment method (from Payment records)
        $byMethod = Payment::whereHas('order', fn ($q) => $q->whereDate('created_at', $date)->where('status', 'paid'))
            ->selectRaw('payment_method, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        $cashTotal = $byMethod->get('cash')?->total ?? 0;
        $cardTotal = $byMethod->get('card')?->total ?? 0;
        $otherTotal = $totalRevenue - $cashTotal - $cardTotal;

        // By order type
        $byType = $orders->groupBy(fn ($o) => $o->type->getLabel())
            ->map(fn ($g) => ['count' => $g->count(), 'total' => $g->sum('total_amount')]);

        // Cancelled orders
        $cancelledCount = Order::whereDate('created_at', $date)->where('status', 'cancelled')->count();

        // Tax estimate
        try {
            $taxRate = app(GeneralSettings::class)->default_tax_rate ?? 10;
        } catch (\Exception) {
            $taxRate = 10;
        }

        return [
            'date' => $date,
            'total_revenue' => $totalRevenue,
            'orders_count' => $ordersCount,
            'avg_ticket' => $avgTicket,
            'cash_total' => $cashTotal,
            'card_total' => $cardTotal,
            'other_total' => $otherTotal > 0 ? $otherTotal : 0,
            'by_type' => $byType,
            'cancelled_count' => $cancelledCount,
            'tax_estimate' => $totalRevenue * ($taxRate / 100),
            'tax_rate' => $taxRate,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print')
                ->label('Imprimer la clôture')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->action(function () {
                    $this->dispatch('print-closure');
                }),
        ];
    }
}
