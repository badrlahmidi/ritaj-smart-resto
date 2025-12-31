<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class PrinterSettings extends Settings
{
    public string $driver; // network, windows
    public ?string $printer_ip_cashier;
    public ?string $printer_ip_kitchen;
    public ?string $printer_ip_bar;
    public bool $open_cash_drawer;
    public bool $double_ticket_kitchen;

    public static function group(): string
    {
        return 'printer';
    }
}
