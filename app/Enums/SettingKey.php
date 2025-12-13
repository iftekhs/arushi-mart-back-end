<?php

namespace App\Enums;

enum SettingKey: string
{
    use EnumUtilities;

    case BUSINESS = 'business';
    case APPLICATION = 'application';
}
