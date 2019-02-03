<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Guild extends Model
{
    protected $fillable = [
        'authorized_roles',
    ];

    protected $hidden = [
        'city_id',
    ];

    protected $casts = [
        'authorized_roles' => 'array',
    ];

    public function authorizedRoles() {
        return $this->map_authorized_roles;
    }

    public function city() {
        return $this->belongsTo('App\Models\City');
    }
}
