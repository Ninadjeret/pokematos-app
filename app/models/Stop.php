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
        if( $this->getActiveRaid() ) {
            return $this->getActiveRaid();
        } elseif( $this->getFutureRaid() ) {
            return $this->getFutureRaid();
        } else {
            return false;
        }
    }

    public function getFutureRaid() {
        $begin = new \DateTime();
        $end = new \DateTime();
        $end->modify('+ 60 minutes');
        $raid = Raid::where('gym_id', $this->id)
            ->where('start_time', '>', $begin->format('Y-m-d H:i:s') )
            ->where('start_time', '<', $end->format('Y-m-d H:i:s') )
            ->first();
        if( empty($raid) ) return false;
        return $raid;
    }

    public function getActiveRaid() {
        $begin = new \DateTime();
        $begin->modify('- 45 minutes');
        $end = new \DateTime();
        $raid = Raid::where('gym_id', $this->id)
            ->where('start_time', '>', $begin->format('Y-m-d H:i:s') )
            ->where('start_time', '<', $end->format('Y-m-d H:i:s') )
            ->first();
        if( empty($raid) ) return false;
        return $raid;
    }

}
