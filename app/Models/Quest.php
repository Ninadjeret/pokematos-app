<?php

namespace App\Models;

use App\Models\Pokemon;
use App\Models\QuestReward;
use Illuminate\Database\Eloquent\Model;

class Quest extends Model
{
    protected $fillable = ['name', 'reward_ids', 'pokemon_ids'];
    protected $appends = ['pokemon', 'pokemons', 'rewards'];

    public function getPokemonsAttribute() {
        if( empty( $this->pokemon_ids ) || !is_array($this->pokemon_ids) ) return false;

        $pokemons = [];
        foreach( $this->pokemon_ids as $pokemon_id ) {
            $pokemon = Pokemon::find($pokemon_id);
            if( $pokemon ) {
                $pokemons[] = $pokemon;
            }
        }
        return $pokemons;
    }

    public function getRewardsAttribute() {
        if( empty( $this->reward_ids ) || !is_array($this->reward_ids) ) return false;

        $rewards = [];
        foreach( $this->reward_ids as $reward_id ) {
            $reward = QuestReward::find($reward_id);
            if( $reward ) {
                $rewards[] = $reward;
            }
        }
        return $rewards;
    }
}
