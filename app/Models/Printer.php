<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Printer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type', // network, usb
        'ip_address', // Legacy (kept for compatibility)
        'path', // New standard field (IP or USB path)
        'port',
        'station_tags', // JSON array of stations (kitchen, bar, etc.)
        'is_active',
    ];

    protected $casts = [
        'station_tags' => 'array',
        'is_active' => 'boolean',
    ];
}
