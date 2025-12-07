<?php

namespace App\Enums;

enum CacheKey: string
{
    case CUSTOMIZATION_SHOW = 'customization.show';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case CANCELED = 'canceled';
}
