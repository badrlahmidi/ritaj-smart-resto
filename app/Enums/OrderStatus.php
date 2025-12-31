<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasLabel, HasColor
{
    case Pending = 'pending';
    case SentToKitchen = 'sent_to_kitchen';
    case Ready = 'ready';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pending => 'En attente',
            self::SentToKitchen => 'En cuisine',
            self::Ready => 'Prêt',
            self::Paid => 'Payé',
            self::Cancelled => 'Annulé',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Pending => 'gray',
            self::SentToKitchen => 'warning',
            self::Ready => 'success',
            self::Paid => 'info',
            self::Cancelled => 'danger',
        };
    }
}
