<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customization extends Model
{
    protected $fillable = [
        'key',
        'label',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];
}
