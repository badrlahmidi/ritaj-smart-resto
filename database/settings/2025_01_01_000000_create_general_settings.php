<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // General
        $this->migrator->add('general.site_name', 'Ritaj Smart Resto');
        $this->migrator->add('general.site_logo', null);
        $this->migrator->add('general.address', 'Casablanca, Maroc');
        $this->migrator->add('general.phone', null);
        $this->migrator->add('general.receipt_footer', 'Merci de votre visite !');
        $this->migrator->add('general.wifi_ssid', null);
        $this->migrator->add('general.wifi_password', null);
        
        // POS
        $this->migrator->add('pos.service_mode', 'standard');
        $this->migrator->add('pos.default_tax_rate', 10.0);
        $this->migrator->add('pos.currency', 'DH');
        $this->migrator->add('pos.allow_negative_stock', false);
        $this->migrator->add('pos.auto_clear_table', true);
        
        // Printer
        $this->migrator->add('printer.driver', 'network');
        $this->migrator->add('printer.printer_ip_cashier', '192.168.1.200');
        $this->migrator->add('printer.printer_ip_kitchen', '192.168.1.201');
        $this->migrator->add('printer.printer_ip_bar', '192.168.1.202');
        $this->migrator->add('printer.open_cash_drawer', true);
        $this->migrator->add('printer.double_ticket_kitchen', false);
        
        // Features
        $this->migrator->add('features.enable_stock_module', true);
        $this->migrator->add('features.enable_kds_module', true);
        $this->migrator->add('features.enable_delivery_module', false);
        $this->migrator->add('features.enable_waiter_tablets', false);
    }
};
