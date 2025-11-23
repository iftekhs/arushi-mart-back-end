<?php

namespace App\Enums;

enum PaymentMethod: string
{
    use EnumUtilities;

    case CASH_ON_DELIVERY = 'cash_on_delivery';
}
