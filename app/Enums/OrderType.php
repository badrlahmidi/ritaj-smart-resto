<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;

enum OrderType: string implements HasLabel, HasColor
{
    case DINE_IN = 'dine_in';
    case TAKEAWAY = 'takeaway';
    case DELIVERY = 'delivery';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DINE_IN => 'À Table',
            self::TAKEAWAY => 'À Emporter',
            self::DELIVERY => 'Livraison',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::DINE_IN => 'info',
            self::TAKEAWAY => 'warning',
            self::DELIVERY => 'success',
        };
    }
}
