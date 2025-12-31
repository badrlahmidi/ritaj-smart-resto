<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Table extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'qr_code_hash',
        'current_order_uuid',
    ];

    public function currentOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'current_order_uuid', 'uuid');
    }
}
