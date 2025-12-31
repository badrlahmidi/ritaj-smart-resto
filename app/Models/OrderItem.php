<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_uuid',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
        'notes',
        'printed_kitchen',
    ];

    protected $casts = [
        'printed_kitchen' => 'boolean',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_uuid', 'uuid');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
