<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneralSetting extends Model
{
    protected $guarded = [];

    // Helper to get settings easily anywhere
    public static function current()
    {
        return static::firstOrCreate([], [
            'restaurant_name' => 'Ritaj Smart Resto',
        ]);
    }
}
