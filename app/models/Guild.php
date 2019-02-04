<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

use App\Models\City;

class Guild extends Model
{
    protected $fillable = ['authorized_roles',];
    protected $hidden = ['city_id'];
    protected $appends = ['city'];
    protected $casts = [
        'authorized_roles' => 'array',
    ];

    public function authorizedRoles() {
        return $this->map_authorized_roles;
    }

    public function getCityAttribute() {
        return City::find($this->city_id);
    }

}
