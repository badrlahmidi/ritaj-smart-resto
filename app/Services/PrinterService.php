<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\DummyPrintConnector;
use Mike42\Escpos\Printer;

class PrinterService
{
    public function getConnector()
    {
        $driver = config('services.printer.driver', 'dummy');

        try {
            switch ($driver) {
                case 'network':
                    $ip = config('services.printer.network.ip');
                    $port = config('services.printer.network.port', 9100);
                    return new NetworkPrintConnector($ip, $port);
                
                case 'windows':
                    $name = config('services.printer.windows.name');
                    // "Microsoft Print to PDF" ou nom d'imprimante USB partagée
                    return new WindowsPrintConnector($name);
                
                case 'dummy':
                default:
                    // Utile pour dev sans imprimante (log only)
                    return new DummyPrintConnector();
            }
        } catch (\Exception $e) {
            Log::error("Printer Connection Failed ($driver): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Imprime un ticket pour la cuisine avec les nouveaux items
     */
    public function printKitchenTicket(Order $order): bool
    {
        // 1. Filtrer les items non imprimés
        $newItems = $order->items()->where('printed_kitchen', false)->get();
        
        if ($newItems->isEmpty()) {
            return false;
        }

        try {
            $connector = $this->getConnector();
            if (!$connector) return false;

            $printer = new Printer($connector);

            // Header
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setTextSize(2, 2);
            $printer->text("CUISINE\n");
            $printer->setTextSize(1, 1);
            $printer->feed();
            
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Table: " . ($order->table?->name ?? 'N/A') . "\n");
            $printer->text("Serveur: " . ($order->waiter?->name ?? 'N/A') . "\n");
            $printer->text("Heure: " . now()->format('H:i') . "\n");
            $printer->text("--------------------------------\n");

            // Body
            foreach ($newItems as $item) {
                $printer->setTextSize(2, 1); // Largeur double pour lisibilité
                $qty = str_pad($item->quantity, 2, ' ', STR_PAD_LEFT);
                $name = $item->product?->name ?? 'Article inconnu';
                
                // Transliteration simple pour éviter problèmes encodage sur certaines imprimantes
                // $name = iconv('UTF-8', 'ASCII//TRANSLIT', $name);
                
                $printer->text("{$qty} x {$name}\n");
                
                if ($item->notes) {
                    $printer->setTextSize(1, 1);
                    $printer->text("   NOTE: {$item->notes}\n");
                }
            }
            $printer->text("--------------------------------\n");
            $printer->feed(3);
            $printer->cut();
            $printer->close();

            // Marquer comme imprimé
            foreach ($newItems as $item) {
                $item->update(['printed_kitchen' => true]);
            }
            
            // Mettre à jour le statut de la commande si nécessaire
            if ($order->status === 'pending') {
                $order->update(['status' => 'sent_to_kitchen']);
            }

            return true;
            
        } catch (\Exception $e) {
            Log::error("Printer Error (Kitchen): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Imprime l'addition client (Ticket de caisse)
     */
    public function printBill(Order $order): bool
    {
        try {
            $connector = $this->getConnector();
            if (!$connector) return false;

            $printer = new Printer($connector);

            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setTextSize(2, 2);
            $printer->text("RITAJ RESTO\n");
            $printer->setTextSize(1, 1);
            $printer->text("123, Avenue Mohamed VI\n");
            $printer->text("Marrakech\n");
            $printer->feed();

            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Ticket: #" . $order->local_id . "\n");
            $printer->text("Table: " . ($order->table?->name ?? 'N/A') . "\n");
            $printer->text("Date: " . now()->format('d/m/Y H:i') . "\n");
            $printer->text("--------------------------------\n");

            // Items
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            foreach ($order->items as $item) {
                $lineTotal = number_format($item->total_price, 2);
                $name = substr($item->product?->name ?? '', 0, 20); // Tronquer nom
                $printer->text(sprintf("%-2s x %-20s %8s\n", $item->quantity, $name, $lineTotal));
            }

            $printer->text("--------------------------------\n");
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->setTextSize(2, 1);
            $printer->text("TOTAL: " . number_format($order->total_amount, 2) . " DH\n");
            $printer->setTextSize(1, 1);
            $printer->feed(2);
            
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("Merci de votre visite !\n");
            $printer->feed(3);
            $printer->cut();
            $printer->close();

            return true;

        } catch (\Exception $e) {
            Log::error("Printer Error (Bill): " . $e->getMessage());
            return false;
        }
    }
}
