<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class PosSettings extends Settings
{
    public string $service_mode; // standard, fast_food
    public float $default_tax_rate;
    public string $currency;
    public bool $allow_negative_stock;
    public bool $auto_clear_table;

    public static function group(): string
    {
        return 'pos';
    }
}
