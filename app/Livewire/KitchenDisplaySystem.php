<?php

namespace App\Livewire;

use App\Models\Order;
use App\Services\PrinterService;
use Livewire\Component;

class KitchenDisplaySystem extends Component
{
    public function getOrdersProperty()
    {
        // On récupère les commandes "envoyées en cuisine" OU "prêtes" récemment (si on veut garder un historique)
        // Mais d'après les screenshots, vos commandes sont passées en 'ready' trop vite ou le filtre est trop strict.
        // Si le statut est 'ready', elles n'apparaissent plus avec le filtre `where('status', 'sent_to_kitchen')`.
        
        return Order::query()
            ->whereIn('status', ['sent_to_kitchen', 'pending']) // Inclure 'pending' au cas où le fix précédent a raté
            ->with(['items.product', 'table', 'waiter'])
            ->orderBy('updated_at', 'asc')
            ->get();
    }

    public function markAsReady(string $orderUuid)
    {
        $order = Order::find($orderUuid);
        if ($order) {
            $order->update(['status' => 'ready']);
            // La commande disparaîtra de l'écran au prochain poll
        }
    }

    public function printTicket(string $orderUuid)
    {
        $order = Order::find($orderUuid);
        if ($order) {
            // On force l'impression même si déjà imprimé
            // Reset flag pour forcer impression
            foreach($order->items as $item) {
                $item->update(['printed_kitchen' => false]);
            }
            
            app(PrinterService::class)->printKitchenTicket($order);
            $this->dispatch('notify', 'Ticket ré-imprimé');
        }
    }

    public function render()
    {
        return view('livewire.kitchen-display-system');
    }
}
