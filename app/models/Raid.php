<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;
use App\Models\Stop;
use App\Models\Pokemon;
use App\Models\Announce;
use App\Models\raidChannel;
use App\Models\RaidMessage;

class Raid extends Model {

    protected $fillable = ['status'];
    protected $hidden = ['gym_id', 'city_id', 'pokemon_id'];
    protected $appends = ['end_time', 'pokemon', 'source', 'channels', 'messages', 'thumbnail_url'];

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

    public function getThumbnailUrlAttribute() {
        return 'https://assets.profchen.fr/img/pokemon/pokemon_icon_'.$this->pokedex_id.'_'.$this->form_id.'.png';
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

    public function getChannelsAttribute() {
        $channels = raidChannel::where('raid_id', $this->id)->get();
        if( $channels ) {
            return $channels;
        }
        return [];
    }

    public function getMessagesAttribute() {
        $messages = RaidMessage::where('raid_id', $this->id)->get();
        if( $messages ) {
            return $messages;
        }
        return [];
    }

    public function getLastAnnounce() {
        $annonce = Announce::where('raid_id', $this->id)
            ->orderBy('created_at', 'desc')
            ->first();
        return $annonce;
    }

    public function getGym() {
        return Stop::find( $this->gym_id );
    }

    public function isFuture() {
        return ( $this->start_time > date('Y-m-d H:i:s') );
    }

    public function getStartTime() {
        return new \DateTime( $this->start_time );
    }

    public function getEndTime() {
        return new \DateTime( $this->end_time );
    }

}
