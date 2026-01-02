<?php

namespace App\Services\Printing;

use App\Models\Order;
use App\Models\Printer;
use App\Settings\GeneralSettings;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer as EscposPrinter;
use Illuminate\Support\Facades\Log;

class ReceiptPrinterService
{
    public function printOrder(Order $order)
    {
        // 1. Find a suitable printer (e.g., tagged 'cashier' or default)
        // For V2 MVP, we take the first active network printer or a specific configured one
        $printerConfig = Printer::where('is_active', true)->first();

        if (!$printerConfig) {
            Log::warning("No active printer found for order #{$order->local_id}");
            return;
        }

        try {
            // 2. Connect
            $connector = null;
            if ($printerConfig->type === 'network') {
                $connector = new NetworkPrintConnector($printerConfig->path, $printerConfig->port ?? 9100);
            } elseif ($printerConfig->type === 'usb' || $printerConfig->type === 'windows') {
                // 'path' would be the Share Name in Windows (e.g., "TM-T20II")
                $connector = new WindowsPrintConnector($printerConfig->path);
            }

            if (!$connector) {
                 throw new \Exception("Invalid printer type");
            }

            $printer = new EscposPrinter($connector);
            $settings = app(GeneralSettings::class);

            // 3. Header
            $printer->setJustification(EscposPrinter::JUSTIFY_CENTER);
            $printer->selectPrintMode(EscposPrinter::MODE_DOUBLE_WIDTH | EscposPrinter::MODE_BOLD);
            $printer->text($settings->site_name . "\n");
            $printer->selectPrintMode(); // Reset
            $printer->text($settings->address . "\n");
            $printer->text($settings->phone . "\n");
            $printer->feed();

            // 4. Info
            $printer->setJustification(EscposPrinter::JUSTIFY_LEFT);
            $printer->text("Ticket: #" . $order->local_id . "\n");
            $printer->text("Date: " . $order->created_at->format('d/m/Y H:i') . "\n");
            $printer->text("Serveur: " . ($order->server->name ?? 'Serveur') . "\n");
            $printer->feed();

            // 5. Items
            $printer->setJustification(EscposPrinter::JUSTIFY_LEFT);
            $printer->text("--------------------------------\n");
            foreach ($order->items as $item) {
                $line = sprintf("%-2s %-18s %8s", $item->quantity, substr($item->product->name, 0, 18), number_format($item->total_price, 2));
                $printer->text($line . "\n");
                if ($item->notes) {
                    $printer->text("   (" . $item->notes . ")\n");
                }
            }
            $printer->text("--------------------------------\n");

            // 6. Total
            $printer->setJustification(EscposPrinter::JUSTIFY_RIGHT);
            $printer->selectPrintMode(EscposPrinter::MODE_DOUBLE_WIDTH | EscposPrinter::MODE_BOLD);
            $printer->text("TOTAL: " . number_format($order->total_amount, 2) . " " . $settings->currency_symbol . "\n");
            $printer->selectPrintMode();
            $printer->feed();

            // 7. Footer
            $printer->setJustification(EscposPrinter::JUSTIFY_CENTER);
            if ($settings->receipt_footer) {
                $printer->text($settings->receipt_footer . "\n");
            }
            if ($settings->wifi_ssid) {
                $printer->text("Wifi: " . $settings->wifi_ssid . "\n");
                $printer->text("Pass: " . $settings->wifi_password . "\n");
            }
            $printer->feed(2);
            
            // 8. Cut & Open Drawer
            $printer->cut();
            $printer->pulse(); 

            $printer->close();

        } catch (\Exception $e) {
            Log::error("Printing failed for Order #{$order->local_id}: " . $e->getMessage());
        }
    }
}
