<?php

namespace App\Models;

use App\Traits\HasStock;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory, HasStock;

    protected $fillable = [
        'name',
        'description',
        'short_description',
        'price',
        'price_takeaway',
        'price_delivery',
        'cost',
        'category_id',
        'is_available',
        'has_stock', // If true, we deduct this product's stock directly (e.g. Can)
        'stock_quantity',
        'alert_threshold',
        'image_url',
        'kitchen_station',
        'is_combo',
    ];

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'product_ingredient')
            ->withPivot(['quantity', 'wastage_percent']);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function optionGroups(): BelongsToMany
    {
        return $this->belongsToMany(OptionGroup::class)
            ->withPivot('sort_order');
    }

    /**
     * Get price based on order type.
     * Uses the specific price columns (price_takeaway / price_delivery) when set;
     * falls back to a percentage modifier of the base price.
     */
    public function getPriceByType(\App\Enums\OrderType $type): float
    {
        return match ($type) {
            \App\Enums\OrderType::TAKEAWAY => $this->price_takeaway ?? round((float) $this->price * 0.9, 2),
            \App\Enums\OrderType::DELIVERY => $this->price_delivery ?? round((float) $this->price * 1.1, 2),
            default => (float) $this->price,
        };
    }

    /**
     * Override HasStock trait method to use 'cost' column
     */
    public function getCostPrice(): float
    {
        return $this->cost ?? 0;
    }
}
