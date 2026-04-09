<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    // Configuration UUID
    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'uuid',
        'local_id',
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
        'synced_at',
        'is_stock_deducted',
        'locked_by',
        'locked_at',
    ];

    protected $casts = [
        'status' => \App\Enums\OrderStatus::class,
        'type' => \App\Enums\OrderType::class,
        'locked_at' => 'datetime',
        'synced_at' => 'datetime',
        'sync_status' => 'boolean',
        'is_stock_deducted' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            if (blank($order->uuid)) {
                $order->uuid = (string) Str::uuid();
            }

            if (blank($order->local_id)) {
                // Use a pessimistic lock to prevent duplicate local_id values under
                // concurrent inserts. Works reliably when this event fires inside an
                // existing DB::transaction() (ProPos, Terminal). For standalone creates
                // the inner transaction wraps the read-modify step atomically.
                $order->local_id = DB::transaction(function () {
                    return ((int) DB::table('orders')->lockForUpdate()->max('local_id') ?? 0) + 1;
                });
            }
        });
    }

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
