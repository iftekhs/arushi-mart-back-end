<?php

namespace App\Enums;

enum ShippingMethod: string
{
    use EnumUtilities;

    case INSIDE_DHAKA = 'inside_dhaka';
    case OUTSIDE_DHAKA = 'outside_dhaka';
}
