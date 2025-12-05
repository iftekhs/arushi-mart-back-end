<?php

namespace App\Enums;

enum ShippingMethod: string
{
    use EnumUtilities;

    case INSIDE_DHAKA = 'inside_dhaka';
    case OUTSIDE_DHAKA = 'outside_dhaka';
    case ON_SITE = 'on_site';

    public static function valuesForUser(): array
    {
        return [
            self::INSIDE_DHAKA->value,
            self::OUTSIDE_DHAKA->value,
        ];
    }
}
