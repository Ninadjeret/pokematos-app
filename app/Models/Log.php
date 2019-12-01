<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $fillable = [
        'city_id',
        'guild_id',
        'type',
        'success',
        'error',
        'source_type',
        'source',
        'result',
    ];
    protected $casts = [
        'result' => 'boolean',
        'success' => 'array',
    ];
}
