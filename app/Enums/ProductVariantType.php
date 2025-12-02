<?php

namespace App\Enums;

enum ProductVariantType: string
{
    use EnumUtilities;

    case STITCHED = 'stitched';
    case UNSTITCHED = 'unstitched';
}
