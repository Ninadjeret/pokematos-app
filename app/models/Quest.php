<?php

namespace App\models;

use App\Models\Pokemon;
use App\Models\QuestReward;
use Illuminate\Database\Eloquent\Model;

class Quest extends Model
{
    protected $fillable = ['name', 'reward_type', 'reward_id', 'pokemon_id'];
    protected $appends = ['pokemon', 'reward'];

    public function getPokemonAttribute() {
        if( $this->reward_type == 'pokemon' && !empty( $this->pokemon_id ) ) {
            $pokemon = Pokemon::find($this->pokemon_id);
            if( $pokemon ) {
                return $pokemon;
            }
        }
        return false;
    }

    public function getRewardAttribute() {
        if( $this->reward_type == 'object' && !empty( $this->reward_id ) ) {
            $reward = QuestReward::find($this->reward_id);
            if( $reward ) {
                return $reward;
            }
        }
        return false;
    }
}
