<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'printer_id',
        'content',
        'status',
        'error_message',
        'attempts',
    ];

    public function printer(): BelongsTo
    {
        return $this->belongsTo(Printer::class);
    }
}
