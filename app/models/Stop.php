<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\City;
use App\Models\Raid;
use App\Models\Zone;

class Stop extends Model {

    protected $appends = ['zone', 'city', 'google_maps_url', 'raid'];
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
    public function getGoogleMapsUrlAttribute() {
        if( $this->lat && $this->lng ) {
            return 'https://www.google.com/maps/search/?api=1&query='.$this->lat.','.$this->lng;
        }
        return false;
    }

    public function getRaidAttribute() {
        $raid = Raid::where('gym_id', $this->id)
            ->where('start_time', '>', date('Y-m-d H:i:s') )
            ->first();
        if( empty($raid) ) return false;
        return $raid;
    }
}
