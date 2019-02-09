<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Stop;
use App\Models\Pokemon;

class Raid extends Model {

    protected $hidden = ['gym_id', 'city_id', 'pokemon_id'];
    protected $appends = ['end_time', 'pokemon'];

    /*public function getGymAttribute() {
        return Stop::find($this->gym_id);
    }*/

    public function getEndTImeAttribute() {
        $endTime = new \DateTime($this->start_time);
        $endTime->modify('+ 45 minutes');
        return $endTime->format('Y-m-d H:i:s');
    }

    public function getPokemonAttribute() {
        if( empty( $this->pokemon_id ) ) {
            return false;
        }
        return Pokemon::find($this->pokemon_id);
    }

}
