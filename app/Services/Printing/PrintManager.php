<?php

namespace App\Services\Printing;

use App\Jobs\ProcessPrintJob;
use App\Models\Order;
use App\Models\Printer;
use App\Models\PrintJob;
use App\Settings\GeneralSettings;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Mike42\Escpos\PrintConnectors\DummyPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer as EscposPrinter;
use Throwable;

class PrintManager
{
    public function queueKitchenTicket(Order $order): bool
    {
        $order->loadMissing('items.product', 'table', 'server', 'user');

        $pendingItems = $order->items
            ->filter(fn ($item) => ! $item->printed_kitchen && $item->status?->value !== 'draft')
            ->values();

        if ($pendingItems->isEmpty()) {
            return true;
        }

        $printers = Printer::query()
            ->where('is_active', true)
            ->get()
            ->filter(function (Printer $printer) use ($pendingItems): bool {
                $tags = $printer->station_tags ?? [];

                if (empty($tags)) {
                    return false;
                }

                return $pendingItems->contains(function ($item) use ($tags): bool {
                    $station = $item->product?->kitchen_station ?? 'kitchen';

                    return in_array($station, $tags, true);
                });
            });

        if ($printers->isEmpty()) {
            Log::warning("No active kitchen printer found for order #{$order->local_id}");

            return false;
        }

        foreach ($printers as $printer) {
            $itemIds = $pendingItems
                ->filter(function ($item) use ($printer): bool {
                    $station = $item->product?->kitchen_station ?? 'kitchen';

                    return in_array($station, $printer->station_tags ?? [], true);
                })
                ->pluck('id')
                ->values()
                ->all();

            if (empty($itemIds)) {
                continue;
            }

            $this->createAndDispatchJob($printer, [
                'type' => 'kitchen_ticket',
                'order_uuid' => $order->uuid,
                'item_ids' => $itemIds,
            ]);
        }

        return true;
    }

    public function queueReceipt(Order $order): bool
    {
        $order->loadMissing('items.product', 'table', 'server', 'user');

        $printer = Printer::query()
            ->where('is_active', true)
            ->get()
            ->first(function (Printer $candidate): bool {
                return in_array('cashier', $candidate->station_tags ?? [], true);
            });

        $printer ??= Printer::query()->where('is_active', true)->first();

        if (! $printer) {
            Log::warning("No active cashier printer found for order #{$order->local_id}");

            return false;
        }

        $this->createAndDispatchJob($printer, [
            'type' => 'receipt',
            'order_uuid' => $order->uuid,
        ]);

        return true;
    }

    public function process(PrintJob $printJob): void
    {
        $payload = json_decode($printJob->content, true, flags: JSON_THROW_ON_ERROR);
        $printerModel = $printJob->printer;

        if (! $printerModel || ! $printerModel->is_active) {
            throw new \RuntimeException('Printer unavailable for print job.');
        }

        $order = Order::with(['items.product', 'table', 'server', 'user'])->findOrFail($payload['order_uuid']);

        $connector = $this->getConnector($printerModel);
        $printer = new EscposPrinter($connector);

        try {
            if ($payload['type'] === 'kitchen_ticket') {
                $items = $order->items->whereIn('id', $payload['item_ids'] ?? []);
                $this->renderKitchenTicket($printer, $order, $items, $printerModel);
                $this->markItemsAsPrinted($items);
            } elseif ($payload['type'] === 'receipt') {
                $this->renderReceipt($printer, $order, $printerModel);
            } else {
                throw new \InvalidArgumentException('Unsupported print job type.');
            }

            $printJob->update([
                'status' => 'printed',
                'attempts' => max(1, $printJob->attempts),
                'error_message' => null,
            ]);
        } finally {
            $printer->close();
        }
    }

    public function testConnection(Printer $printer): bool
    {
        try {
            if ($printer->type !== 'network') {
                return true;
            }

            $handle = @fsockopen($printer->path ?: $printer->ip_address, (int) ($printer->port ?? 9100), $errno, $error, 2);

            if (! $handle) {
                return false;
            }

            fclose($handle);

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function createAndDispatchJob(Printer $printer, array $payload): PrintJob
    {
        $printJob = PrintJob::create([
            'printer_id' => $printer->id,
            'content' => json_encode($payload, JSON_THROW_ON_ERROR),
            'status' => 'pending',
            'attempts' => 0,
        ]);

        if (config('queue.default') === 'sync' || app()->runningUnitTests()) {
            ProcessPrintJob::dispatchSync($printJob->id);
        } else {
            ProcessPrintJob::dispatch($printJob->id);
        }

        return $printJob;
    }

    private function getConnector(Printer $printerModel)
    {
        return match ($printerModel->type) {
            'network' => new NetworkPrintConnector($printerModel->path ?: $printerModel->ip_address, $printerModel->port ?? 9100),
            'windows', 'usb' => new WindowsPrintConnector($printerModel->path ?: $printerModel->name),
            default => new DummyPrintConnector,
        };
    }

    private function renderKitchenTicket(EscposPrinter $printer, Order $order, Collection $items, Printer $printerModel): void
    {
        $printer->setJustification(EscposPrinter::JUSTIFY_CENTER);
        $printer->setTextSize(2, 2);
        $printer->text("COMMANDE\n");
        $printer->setTextSize(1, 1);
        $printer->text(strtoupper($printerModel->name)."\n");
        $printer->feed();

        $printer->setJustification(EscposPrinter::JUSTIFY_LEFT);
        $printer->text('Table: '.($order->table?->name ?? 'N/A')."\n");
        $printer->text('Serveur: '.($order->server?->name ?? $order->user?->name ?? 'N/A')."\n");
        $printer->text('Ref: #'.$order->local_id."\n");
        $printer->text('Date: '.now()->format('d/m H:i')."\n");
        $printer->text(str_repeat('-', 32)."\n");

        foreach ($items as $item) {
            $printer->setTextSize(2, 1);
            $printer->text(str_pad((string) $item->quantity, 2, ' ', STR_PAD_LEFT).' '.mb_substr($item->product?->name ?? 'Article', 0, 20)."\n");

            if (! empty($item->options)) {
                $printer->setTextSize(1, 1);
                foreach ($item->options as $option) {
                    $printer->text('   + '.($option['name'] ?? 'Option')."\n");
                }
            }

            if ($item->notes) {
                $printer->setTextSize(1, 1);
                $printer->setReverseColors(true);
                $printer->text(" NOTE: {$item->notes} \n");
                $printer->setReverseColors(false);
            }
        }

        $printer->text(str_repeat('-', 32)."\n");
        $printer->feed(3);

        if ($printerModel->auto_cut ?? true) {
            $printer->cut();
        }
    }

    private function renderReceipt(EscposPrinter $printer, Order $order, Printer $printerModel): void
    {
        $settings = app(GeneralSettings::class);
        $subtotal = $order->items->sum('total_price');
        $discount = (float) ($order->discount_amount ?? 0);
        $tax = (float) ($order->tax_amount ?? 0);
        $serviceCharge = (float) ($order->service_charge ?? 0);

        $printer->setJustification(EscposPrinter::JUSTIFY_CENTER);
        $printer->selectPrintMode(EscposPrinter::MODE_DOUBLE_WIDTH | EscposPrinter::MODE_BOLD);
        $printer->text(($settings->site_name ?? config('app.name'))."\n");
        $printer->selectPrintMode();
        $printer->text(($settings->address ?? '')."\n");
        $printer->text(($settings->phone ?? '')."\n");
        $printer->feed();

        $printer->setJustification(EscposPrinter::JUSTIFY_LEFT);
        $printer->text('Ticket: #'.$order->local_id."\n");
        $printer->text('Date: '.$order->created_at?->format('d/m/Y H:i')."\n");
        $printer->text('Serveur: '.($order->server?->name ?? $order->user?->name ?? 'Serveur')."\n");
        $printer->text(str_repeat('-', 32)."\n");

        foreach ($order->items as $item) {
            $line = sprintf(
                '%-2s %-18s %8s',
                $item->quantity,
                mb_substr($item->product?->name ?? 'Produit', 0, 18),
                number_format((float) $item->total_price, 2)
            );
            $printer->text($line."\n");

            if ($item->notes) {
                $printer->text('   ('.$item->notes.")\n");
            }
        }

        $printer->text(str_repeat('-', 32)."\n");
        $printer->text(sprintf("%-22s %8s\n", 'Sous-total', number_format((float) $subtotal, 2)));
        $printer->text(sprintf("%-22s %8s\n", 'Remise', number_format($discount, 2)));
        $printer->text(sprintf("%-22s %8s\n", 'TVA', number_format($tax, 2)));
        $printer->text(sprintf("%-22s %8s\n", 'Service', number_format($serviceCharge, 2)));
        $printer->setJustification(EscposPrinter::JUSTIFY_RIGHT);
        $printer->selectPrintMode(EscposPrinter::MODE_DOUBLE_WIDTH | EscposPrinter::MODE_BOLD);
        $printer->text('TOTAL: '.number_format((float) $order->total_amount, 2).' '.($settings->currency_symbol ?? 'MAD')."\n");
        $printer->selectPrintMode();
        $printer->feed();

        $printer->setJustification(EscposPrinter::JUSTIFY_CENTER);
        if (! empty($settings->receipt_footer)) {
            $printer->text($settings->receipt_footer."\n");
        }

        if (! empty($settings->wifi_ssid)) {
            $printer->text('Wifi: '.$settings->wifi_ssid."\n");
            $printer->text('Pass: '.($settings->wifi_password ?? '')."\n");
        }

        $printer->feed(2);

        if ($printerModel->auto_cut ?? true) {
            $printer->cut();
        }

        if ($printerModel->cash_drawer ?? false) {
            $printer->pulse();
        }
    }

    private function markItemsAsPrinted(Collection $items): void
    {
        foreach ($items as $item) {
            $item->update([
                'printed_kitchen' => true,
                'printed_at' => now(),
            ]);
        }
    }
}
