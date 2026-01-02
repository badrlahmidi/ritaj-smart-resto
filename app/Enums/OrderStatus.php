<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasLabel, HasColor
{
    case Draft = 'draft';
    case SentToKitchen = 'sent_to_kitchen';
    case InService = 'in_service';
    case PaymentPending = 'payment_pending';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Draft => 'Brouillon',
            self::SentToKitchen => 'En cuisine',
            self::InService => 'En service',
            self::PaymentPending => 'Addition demandée',
            self::Paid => 'Payé',
            self::Cancelled => 'Annulé',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::SentToKitchen => 'warning',
            self::InService => 'info',
            self::PaymentPending => 'danger',
            self::Paid => 'success',
            self::Cancelled => 'danger',
        };
    }
}
