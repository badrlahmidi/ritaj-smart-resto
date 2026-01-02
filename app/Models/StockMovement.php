<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'stockable_id',
        'stockable_type',
        'type', // purchase, sale, waste, adjustment
        'quantity',
        'cost',
        'reference',
        'user_id',
    ];

    public function stockable()
    {
        return $this->morphTo();
    }
}
