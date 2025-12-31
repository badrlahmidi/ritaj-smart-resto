<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Printer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'ip_address',
        'port',
        'path',
        'station_tags',
        'is_active',
    ];

    protected $casts = [
        'station_tags' => 'array',
        'is_active' => 'boolean',
    ];

    public function jobs(): HasMany
    {
        return $this->hasMany(PrintJob::class);
    }
}
