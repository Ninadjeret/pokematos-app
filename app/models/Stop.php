<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stop extends Model {

    protected $casts = [
        'ex' => 'boolean',
        'gym' => 'boolean',
    ];

}
