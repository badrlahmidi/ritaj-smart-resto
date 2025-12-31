<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;
    protected $guarded = [];
}

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

class PurchaseItem extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}
