<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OptionGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_multiselect',
        'max_options',
        'is_required',
    ];

    protected $casts = [
        'is_multiselect' => 'boolean',
        'is_required' => 'boolean',
    ];

    public function options(): HasMany
    {
        return $this->hasMany(Option::class);
    }
}
