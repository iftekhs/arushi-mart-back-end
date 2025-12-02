<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customization extends Model
{
    protected $fillable = [
        'key',
        'label',
        'fields',
        'value',
    ];

    protected $casts = [
        'fields' => 'array',
        'value' => 'array',
    ];
}
