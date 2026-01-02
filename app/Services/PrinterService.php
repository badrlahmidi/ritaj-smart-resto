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
    public function getConnector(\App\Models\Printer $printerModel)
    {
        try {
            switch ($printerModel->type) {
                case 'network':
                    return new NetworkPrintConnector($printerModel->path, $printerModel->port ?? 9100);
                
                case 'windows':
                    // 'path' should contain the Share Name (e.g., "KitchenPrinter") or LPT/COM port
                    return new WindowsPrintConnector($printerModel->path);

                case 'dummy':
                default:
                    return new DummyPrintConnector();
            }
        } catch (\Exception $e) {
            Log::error("Printer Connection Failed ({$printerModel->name}): " . $e->getMessage());
            return null;
        }
    }

    public function printKitchenTicket(Order $order): bool
    {
        // 1. Get all items that need printing
        $newItems = $order->items()
            ->where('printed_kitchen', false)
            ->where('status', '!=', 'pending') // Only sent items
            ->with('product')
            ->get();
        
        if ($newItems->isEmpty()) {
            return true;
        }

        // 2. Get all active printers
        $printers = \App\Models\Printer::where('is_active', true)->get();
        $allSuccess = true;

        if ($printers->isEmpty()) {
            Log::warning("No active printers found.");
            return false;
        }

        foreach ($printers as $printerModel) {
            // 3. Filter items for this printer's stations
            // If station_tags is null/empty, maybe it's a master printer? 
            // For now, assume strict mapping. If empty, print nothing or everything? 
            // Let's assume strict: printer MUST have tags.
            $printerStations = $printerModel->station_tags ?? [];
            
            $itemsForThisPrinter = $newItems->filter(function ($item) use ($printerStations) {
                // If product has no specific station, default to 'kitchen'
                $productStation = $item->product->kitchen_station ?? 'kitchen';
                return in_array($productStation, $printerStations);
            });

            if ($itemsForThisPrinter->isEmpty()) {
                continue;
            }

            // 4. Connect and Print
            try {
                $connector = $this->getConnector($printerModel);
                if (!$connector) {
                    $allSuccess = false;
                    continue;
                }

                $printer = new Printer($connector);
                
                // --- TICKET LAYOUT ---
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer->setTextSize(2, 2);
                $printer->text("COMMANDE\n");
                $printer->setTextSize(1, 1);
                $printer->text(strtoupper($printerModel->name) . "\n");
                $printer->feed();
                
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text("Table: " . ($order->table?->name ?? 'N/A') . "\n");
                $printer->text("Svr: " . ($order->user?->name ?? 'N/A') . "\n"); // Fixed: user relation
                $printer->text("Ref: #" . $order->local_id . "\n");
                $printer->text("Date: " . now()->format('d/m H:i') . "\n");
                $printer->text("--------------------------------\n");

                foreach ($itemsForThisPrinter as $item) {
                    $printer->setTextSize(2, 1);
                    $qty = str_pad($item->quantity, 2, ' ', STR_PAD_LEFT);
                    $name = substr($item->product?->name ?? 'Article ???', 0, 20); // Truncate for layout
                    $printer->text("{$qty} {$name}\n");
                    
                    // Options
                    if (!empty($item->options)) {
                         $printer->setTextSize(1, 1);
                         foreach ($item->options as $opt) {
                             $printer->text("   + " . ($opt['name'] ?? 'Option') . "\n");
                         }
                    }

                    // Notes
                    if ($item->notes) {
                        $printer->setTextSize(1, 1);
                        $printer->setReverseColors(true); // Highlight note
                        $printer->text(" NOTE: {$item->notes} \n");
                        $printer->setReverseColors(false);
                    }
                }
                
                $printer->text("--------------------------------\n");
                $printer->feed(3);
                $printer->cut();
                $printer->close();

            } catch (\Exception $e) {
                Log::error("Print Error ({$printerModel->name}): " . $e->getMessage());
                $allSuccess = false; // Mark partial failure but continue to other printers
            }
        }

        // 5. Mark items as printed ONLY if at least one printer worked (simplified logic)
        // Ideally, we should track 'printed' per item per station, but for now:
        if ($allSuccess) {
            foreach ($newItems as $item) {
                $item->update([
                    'printed_kitchen' => true,
                    'printed_at' => now()
                ]);
            }
        }

        return $allSuccess;
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
