<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $site_name;
    public ?string $site_logo;
    public ?string $address;
    public ?string $phone;
    public ?string $receipt_footer;
    public ?string $wifi_ssid;
    public ?string $wifi_password;
    
    public static function group(): string
    {
        return 'general';
    }
}
