<?php

namespace App\Enums;

enum CacheKey: string
{
    case CUSTOMIZATION_SHOW = 'customization.show';
    case SETTING_SHOW = 'setting.show';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case CANCELED = 'canceled';
}
