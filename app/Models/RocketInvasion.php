<?php

namespace App\Models;

use App\Models\Pokemon;
use App\Models\RocketBoss;
use App\Models\RocketMessage;
use Illuminate\Database\Eloquent\Model;

class RocketInvasion extends Model
{
    protected $table = 'rocket_invasions';
    protected $fillable = [
        'city_id',
        'stop_id',
        'date',
        'boss_id',
        'pokemon_step1',
        'pokemon_step2',
        'pokemon_step3',
    ];
    protected $appends = ['boss'];
    protected $hidden = ['boss_id'];

    public function getBossAttribute() {
        if( empty($this->boss_id) ) {
            return false;
        }
        $boss = RocketBoss::find($this->boss_id);
        if( empty($boss) ) {
            return false;
        }
        return $boss;
    }

    public function getPokemonStep1Attribute($value) {
        if( empty($value) ) {
            return false;
        }
        $pokemon = Pokemon::find($value);
        if( empty($pokemon) ) {
            return false;
        }
        return $pokemon;
    }

    public function getPokemonStep2Attribute($value) {
        if( empty($value) ) {
            return false;
        }
        $pokemon = Pokemon::find($value);
        if( empty($pokemon) ) {
            return false;
        }
        return $pokemon;
    }

    public function getPokemonStep3Attribute($value) {
        if( empty($value) ) {
            return false;
        }
        $pokemon = Pokemon::find($value);
        if( empty($pokemon) ) {
            return false;
        }
        return $pokemon;
    }

    public function getStop() {
        return Stop::find( $this->stop_id );
    }

    public function getUserActions() {
        $annonces = UserAction::where('type', 'like', 'rocket-invasion-%')
            ->where('relation_id', $this->id)
            ->orderBy('created_at', 'asc')
            ->get();
        return $annonces;
    }

    public function getLastUserAction( $include_auto = false ) {
        if( $include_auto ) {
            $annonce = UserAction::where('type', 'like', 'rocket-invasion-%')
                ->where('relation_id', $this->id)
                ->orderBy('created_at', 'desc')
                ->first();
            return $annonce;
        }
        $annonce = UserAction::where('type', 'like', 'rocket-invasion-%')
            ->where('relation_id', $this->id)
            ->where('source', '!=', 'auto')
            ->orderBy('created_at', 'desc')
            ->first();
        return $annonce;
    }

    public function getMessagesAttribute() {
        $messages = RocketMessage::where('invasion_id', $this->id)->get();
        if( $messages ) {
            return $messages;
        }
        return [];
    }
}
