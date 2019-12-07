<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model {

    protected $fillable = ['role_category_id', 'channels', 'type', 'roles' ];
    protected $casts = [
        'channels' => 'array',
        'roles' => 'array',
    ];

}
