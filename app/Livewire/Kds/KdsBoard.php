<?php

namespace App\Livewire\Kds;

use App\Models\Order;
use App\Models\OrderItem;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.kds')]
class KdsBoard extends Component
{
    public $stationFilter = null; // 'kitchen', 'pizza', 'bar'

    public function mount($station = null)
    {
        $this->stationFilter = $station;
    }

    #[Computed]
    public function pendingOrders()
    {
        $activeItemStatuses = [
            \App\Enums\OrderItemStatus::Sent->value,
            \App\Enums\OrderItemStatus::Prepared->value,
        ];

        return Order::whereHas('items', function ($q) use ($activeItemStatuses) {
            $q->whereIn('status', $activeItemStatuses);
            if ($this->stationFilter) {
                $q->whereHas('product', fn ($sq) => $sq->where('kitchen_station', $this->stationFilter));
            }
        })
            ->whereIn('status', [
                \App\Enums\OrderStatus::SentToKitchen->value,
                \App\Enums\OrderStatus::InService->value,
            ])
            ->with(['items' => function ($q) use ($activeItemStatuses) {
                $q->whereIn('status', $activeItemStatuses);
                if ($this->stationFilter) {
                    $q->whereHas('product', fn ($sq) => $sq->where('kitchen_station', $this->stationFilter));
                }
            }, 'table', 'server'])
            ->orderBy('updated_at', 'asc')
            ->get();
    }

    public function markItemReady($itemId)
    {
        $item = OrderItem::find($itemId);
        if (! $item) {
            return;
        }

        // Cycle: Sent → Prepared → Served
        $next = match ($item->status) {
            \App\Enums\OrderItemStatus::Sent => \App\Enums\OrderItemStatus::Prepared,
            \App\Enums\OrderItemStatus::Prepared => \App\Enums\OrderItemStatus::Served,
            default => null,
        };

        if ($next) {
            $item->update(['status' => $next->value]);
        }

        $this->checkOrderCompletion($item->order_uuid);
    }

    public function markOrderReady($orderUuid)
    {
        $order = Order::where('uuid', $orderUuid)->first();
        if (! $order) {
            return;
        }

        // Mark all filtered items as Prepared → Served
        foreach ($order->items as $item) {
            if (! in_array($item->status, [\App\Enums\OrderItemStatus::Sent, \App\Enums\OrderItemStatus::Prepared])) {
                continue;
            }

            if ($this->stationFilter && $item->product?->kitchen_station !== $this->stationFilter) {
                continue;
            }

            $item->update(['status' => \App\Enums\OrderItemStatus::Served->value]);
        }

        $order->refresh();

        // If all items in order are served, update order status
        if ($order->items()->whereNotIn('status', [\App\Enums\OrderItemStatus::Served->value, \App\Enums\OrderItemStatus::Cancelled->value])->count() === 0) {
            $order->update(['status' => \App\Enums\OrderStatus::InService]);
        }

        $this->dispatch('notify', 'Commande terminée !', 'success');
    }

    protected function checkOrderCompletion(string $orderUuid): void
    {
        $order = Order::where('uuid', $orderUuid)->first();
        if (! $order) {
            return;
        }

        $hasActiveItems = $order->items()
            ->whereNotIn('status', [
                \App\Enums\OrderItemStatus::Served->value,
                \App\Enums\OrderItemStatus::Cancelled->value,
            ])
            ->exists();

        if (! $hasActiveItems) {
            $order->update(['status' => \App\Enums\OrderStatus::InService]);
        }
    }

    // Listen for events from Reverb (WebSockets)
    public function getListeners()
    {
        return [
            'echo:kitchen,NewOrderForKitchen' => '$refresh',
            'echo:kitchen,OrderVoided' => '$refresh',
        ];
    }

    public function render()
    {
        return view('livewire.kds.kds-board');
    }
}
