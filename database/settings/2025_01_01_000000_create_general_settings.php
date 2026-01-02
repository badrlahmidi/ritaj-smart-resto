<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // On s'assure que tout est dans le groupe 'general' pour correspondre au Model
        $this->migrator->add('general.site_name', 'Ritaj Smart Resto');
        $this->migrator->add('general.site_logo', null);
        $this->migrator->add('general.address', 'Casablanca, Maroc');
        $this->migrator->add('general.phone', null);
        $this->migrator->add('general.email', null);
        $this->migrator->add('general.receipt_footer', 'Merci de votre visite !');
        $this->migrator->add('general.wifi_ssid', null);
        $this->migrator->add('general.wifi_password', null);
        
        $this->migrator->add('general.default_tax_rate', 10.0);
        $this->migrator->add('general.currency_symbol', 'DH');
        
        $this->migrator->add('general.enable_stock_management', true);
        $this->migrator->add('general.enable_kds', true);
        $this->migrator->add('general.enable_delivery', true);
        $this->migrator->add('general.enable_takeaway', true);
    }
};