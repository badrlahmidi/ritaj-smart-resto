<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Printer as PrinterModel;
use App\Services\Printing\PrintManager;
use App\Services\Printing\ReceiptPrinterService;
use Illuminate\Support\Facades\Log;
use Mike42\Escpos\PrintConnectors\DummyPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class PrinterService
{
    public function __construct(
        private readonly PrintManager $printManager,
        private readonly ReceiptPrinterService $receiptPrinterService
    ) {}

    public function getConnector(PrinterModel $printerModel)
    {
        try {
            switch ($printerModel->type) {
                case 'network':
                    return new NetworkPrintConnector($printerModel->path, $printerModel->port ?? 9100);

                case 'windows':
                    return new WindowsPrintConnector($printerModel->path);

                case 'dummy':
                default:
                    return new DummyPrintConnector;
            }
        } catch (\Exception $e) {
            Log::error("Printer Connection Failed ({$printerModel->name}): ".$e->getMessage());

            return null;
        }
    }

    public function printKitchenTicket(Order $order): bool
    {
        return $this->printManager->queueKitchenTicket($order);
    }

    public function printBill(Order $order): bool
    {
        try {
            return $this->receiptPrinterService->printOrder($order);
        } catch (\Throwable $e) {
            Log::error('Printer Error (Bill): '.$e->getMessage());

            return false;
        }
    }
}
