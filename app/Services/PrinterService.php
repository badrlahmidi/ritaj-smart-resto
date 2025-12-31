<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\DummyPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
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
                    return new WindowsPrintConnector($name);

                case 'file':
                    // Écrit dans storage/app/printer_output.txt (Idéal pour test local Windows)
                    $path = storage_path('app/printer_output.txt');
                    return new FilePrintConnector($path);
                
                case 'dummy':
                default:
                    return new DummyPrintConnector();
            }
        } catch (\Exception $e) {
            Log::error("Printer Connection Failed ($driver): " . $e->getMessage());
            return null;
        }
    }

    public function printKitchenTicket(Order $order): bool
    {
        $newItems = $order->items()->where('printed_kitchen', false)->get();
        
        if ($newItems->isEmpty()) {
            Log::info("Order {$order->uuid}: No new items to print.");
            return true; // Rien à imprimer, ce n'est pas une erreur
        }

        try {
            $connector = $this->getConnector();
            if (!$connector) {
                Log::error("Order {$order->uuid}: No connector available.");
                return false;
            }

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
                $printer->setTextSize(2, 1);
                $qty = str_pad($item->quantity, 2, ' ', STR_PAD_LEFT);
                $name = $item->product?->name ?? 'Article inconnu';
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
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("Printer Error (Kitchen): " . $e->getMessage());
            return false;
        }
    }

    public function printBill(Order $order): bool
    {
        try {
            $connector = $this->getConnector();
            if (!$connector) return false;

            $printer = new Printer($connector);
            // ... (Logic bill same as before)
            // Pour simplifier l'exemple ici, on ferme juste
            $printer->text("TICKET DE CAISSE SIMULÉ\n");
            $printer->cut();
            $printer->close();

            return true;
        } catch (\Exception $e) {
            Log::error("Printer Error (Bill): " . $e->getMessage());
            return false;
        }
    }
}
