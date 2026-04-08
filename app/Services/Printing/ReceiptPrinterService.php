<?php

namespace App\Services\Printing;

use App\Models\Order;

class ReceiptPrinterService
{
    public function __construct(
        private readonly PrintManager $printManager
    ) {}

    public function printOrder(Order $order): bool
    {
        return $this->printManager->queueReceipt($order);
    }
}
