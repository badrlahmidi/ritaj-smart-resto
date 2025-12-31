<?php

namespace App\Models;

use App\Enums\OrderType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'price',
        'price_takeaway',
        'price_delivery',
        'image_url',
        'is_available',
        'stock_quantity',
        'track_stock',
        'alert_threshold',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'price_takeaway' => 'decimal:2',
        'price_delivery' => 'decimal:2',
        'is_available' => 'boolean',
        'track_stock' => 'boolean',
        'stock_quantity' => 'integer',
        'alert_threshold' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function ingredients(): BelongsToMany
    {
        // Explicitly define 'product_ingredient' as the pivot table name
        return $this->belongsToMany(Ingredient::class, 'product_ingredient')
            ->withPivot(['quantity', 'unit'])
            ->withTimestamps();
    }

    public function getPriceByType(string|OrderType $type): float
    {
        // Si on passe une string, on essaie de la convertir en Enum, sinon défaut DINE_IN
        $typeEnum = is_string($type) ? OrderType::tryFrom($type) : $type;
        
        if (!$typeEnum) {
            return (float) $this->price; // Fallback sécurité
        }
    
        return match ($typeEnum) {
            // Logique de cascade (Null Coalescing)
            // Si price_takeaway est null ?? on prend this->price
            OrderType::TAKEAWAY => (float) ($this->price_takeaway ?? $this->price),
            
            // Si price_delivery est null ?? on prend this->price
            OrderType::DELIVERY => (float) ($this->price_delivery ?? $this->price),
            
            // Par défaut (Dine-in)
            default => (float) $this->price,
        };
    }
}
