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
        'customer_name',
        'customer_phone',
        'customer_address',
        'status',
        'type',
        'total_amount',
        'discount_amount',
        'discount_type',
        'service_charge',
        'tax_amount',
        'payment_status',
        'payment_method',
        'notes',
        'cancel_reason',
        'sync_status',
        'locked_by',
        'locked_at',
    ];

    protected $casts = [
        'status' => \App\Enums\OrderStatus::class,
        'type' => \App\Enums\OrderType::class,
        'locked_at' => 'datetime',
        'sync_status' => 'boolean',
    ];

    public function locker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function isLockedByOthers(): bool
    {
        return $this->locked_by && $this->locked_by !== auth()->id() && $this->locked_at > now()->subMinutes(5);
    }

    public function items(): HasMany
    {
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

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'order_uuid', 'uuid');
    }
}