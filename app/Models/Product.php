<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'short_description',
        'price',
        'cost',
        'category_id',
        'is_available',
        'has_stock',
        'stock_quantity',
        'min_stock_alert',
        'image_url',
        'kitchen_station',
        'is_combo',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function optionGroups(): BelongsToMany
    {
        return $this->belongsToMany(OptionGroup::class)
            ->withPivot('sort_order')
            ->orderBy('pivot_sort_order');
    }

    /**
     * Get price based on order type (e.g., takeaway discount or delivery markup)
     */
    public function getPriceByType(\App\Enums\OrderType $type): float
    {
        return match ($type) {
            \App\Enums\OrderType::TAKEAWAY => $this->price * 0.9, // -10%
            \App\Enums\OrderType::DELIVERY => $this->price * 1.1, // +10%
            default => $this->price,
        };
    }
}
