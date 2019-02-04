<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\City;
use App\Models\Zone;

class Stop extends Model {

    protected $appends = ['zone', 'city'];
    protected $hidden = ['zone_id', 'city_id'];
    protected $casts = [
        'ex' => 'boolean',
        'gym' => 'boolean',
    ];

    public function getZoneAttribute() {
        return Zone::find($this->zone_id);
    }

    public function getCityAttribute() {
        return City::find($this->city_id);
    }
}
