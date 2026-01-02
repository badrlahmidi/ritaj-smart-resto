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
        'options',
        'notes',
        'status',
        'printed_kitchen',
        'printed_at',
    ];

    protected $casts = [
        'options' => 'array',
        'status' => \App\Enums\OrderItemStatus::class,
        'printed_kitchen' => 'boolean',
        'printed_at' => 'datetime',
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
