<?php

namespace App\Enums;

enum ShippingStatus: string
{
    use EnumUtilities;

    case PENDING = 'pending';
    case PACKAGING = 'packaging';
    case ON_THE_WAY = 'on_the_way';
    case DELIVERED = 'delivered';
    case CANCELED = 'canceled';
}
