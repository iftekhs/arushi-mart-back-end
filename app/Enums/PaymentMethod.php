<?php

namespace App\Enums;

enum PaymentMethod: string
{
    use EnumUtilities;

    case CASH_ON_DELIVERY = 'cash_on_delivery';
    case CASH = 'cash';

    public static function valuesForUser(): array
    {
        return [
            self::CASH_ON_DELIVERY->value,
        ];
    }
}
