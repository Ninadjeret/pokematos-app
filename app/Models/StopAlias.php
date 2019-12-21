<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StopAlias extends Model
{
    protected $table = 'stop_aliases';
    protected $fillable = [
        'stop_id',
        'name',
    ];
}
