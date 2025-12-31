<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    // Configuration UUID
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'uuid',
        'user_id',
        'table_id',
        'status',
        'type',
        'total_amount',
        'payment_status',
    ];

    public function items(): HasMany
    {
        // Relation avec OrderItem via uuid
        return $this->hasMany(OrderItem::class, 'order_uuid', 'uuid');
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
