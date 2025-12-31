<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class FeatureSettings extends Settings
{
    public bool $enable_stock_module;
    public bool $enable_kds_module;
    public bool $enable_delivery_module;
    public bool $enable_waiter_tablets;

    public static function group(): string
    {
        return 'features';
    }
}
