<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }
    
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
