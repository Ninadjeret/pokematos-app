<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;
use App\Models\Stop;
use App\Models\Pokemon;
use App\Models\Announce;

class Raid extends Model {

    protected $hidden = ['gym_id', 'city_id', 'pokemon_id'];
    protected $appends = ['end_time', 'pokemon', 'source'];

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

    public function getSourceAttribute() {
        $annonce = $this->getLastAnnounce();
        if( empty( $annonce ) ) return false;
        $return = [
            'source' => $annonce->source,
            'user' => User::find($annonce->user_id),
        ];
        return $return;
    }

    public function getLastAnnounce() {
        $annonce = Announce::where('raid_id', $this->id)
            ->orderBy('created_at', 'desc')
            ->first();
        return $annonce;
    }

}
