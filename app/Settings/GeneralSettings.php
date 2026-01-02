<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    // Identité (Groupe general)
    public string $site_name;
    public ?string $site_logo;
    public ?string $address;
    public ?string $phone;
    public ?string $email;

    // POS Settings (Mappé via migration)
    public float $default_tax_rate;
    public string $currency_symbol;
    
    // Modules (Groupe features dans migration, mappé ici)
    public bool $enable_stock_management;
    public bool $enable_delivery;
    public bool $enable_takeaway;
    public bool $enable_kds;

    // Impression
    public ?string $receipt_footer;
    public ?string $wifi_ssid;
    public ?string $wifi_password;
    
    public static function group(): string
    {
        return 'general';
    }
}