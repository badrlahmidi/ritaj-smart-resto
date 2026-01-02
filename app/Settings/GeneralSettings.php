<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    // Identité
    public string $site_name;
    public ?string $site_logo;
    public ?string $address;
    public ?string $phone;
    public ?string $email;
    public ?string $facebook_url;
    public ?string $instagram_url;

    // Finance
    public string $currency_symbol;
    public float $default_tax_rate; // TVA par défaut (ex: 10%)
    
    // Modules (ON/OFF)
    public bool $enable_stock_management;
    public bool $enable_delivery;
    public bool $enable_takeaway;
    public bool $enable_kds; // Kitchen Display System

    // Impression
    public ?string $receipt_footer;
    public ?string $wifi_ssid;
    public ?string $wifi_password;
    
    public static function group(): string
    {
        return 'general';
    }
}
