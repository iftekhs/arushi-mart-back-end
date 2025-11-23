<?php

namespace App\Enums;

trait EnumUtilities
{
    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
