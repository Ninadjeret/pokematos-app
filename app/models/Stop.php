<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\City;
use App\Models\Raid;
use App\Models\Quest;
use App\Models\QuestInstance;
use App\Models\Zone;
use App\Models\raidChannel;

class Stop extends Model {

    protected $fillable = ['name', 'niantic_name', 'description', 'lat', 'lng', 'ex', 'gym', 'city_id', 'zone_id', 'ex'];
    protected $appends = ['zone', 'city', 'google_maps_url', 'raid', 'quest'];
    protected $hidden = ['zone_id', 'city_id', 'quest_id'];
    protected $casts = [
        'ex' => 'boolean',
        'gym' => 'boolean',
    ];

    public function raids() {
        return $this->hasMany('App\Models\Raid', 'gym_id');
    }

    public function quests() {
        return $this->hasMany('App\Models\QuestInstance', 'gym_id');
    }

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

    public function getQuestAttribute() {
        $questInstance = QuestInstance::where('gym_id', $this->id)
            ->where('date', date('Y-m-d 00:00:00') )
            ->first();
        if( !empty($questInstance) ) {
            return $questInstance;
        }
        return false;
    }

    public function getFutureRaid() {
        $begin = new \DateTime();
        $end = new \DateTime();
        $end->modify('+ 60 minutes');
        $raid = Raid::where('gym_id', $this->id)
            ->where('start_time', '>', $begin->format('Y-m-d H:i:s') )
            ->where('start_time', '<', $end->format('Y-m-d H:i:s') )
            ->first();
        if( !empty($raid) ) return $raid;

        $raidEx = Raid::where('gym_id', $this->id)
            ->where('start_time', '>', $begin->format('Y-m-d H:i:s') )
            ->where('ex', '1')
            ->first();
        if( !empty($raidEx) ) return $raidEx;

        return false;
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
