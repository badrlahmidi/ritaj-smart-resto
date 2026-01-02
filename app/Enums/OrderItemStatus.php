<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OrderItemStatus: string implements HasLabel, HasColor
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Prepared = 'prepared';
    case Served = 'served';
    case Cancelled = 'cancelled';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Draft => 'En attente',
            self::Sent => 'Envoyé',
            self::Prepared => 'Prêt',
            self::Served => 'Servi',
            self::Cancelled => 'Annulé',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Sent => 'warning',
            self::Prepared => 'success',
            self::Served => 'info',
            self::Cancelled => 'danger',
        };
    }
}
