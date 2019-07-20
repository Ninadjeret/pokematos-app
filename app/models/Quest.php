<?php

namespace App\models;

use App\Models\Pokemon;
use Illuminate\Database\Eloquent\Model;

class Quest extends Model
{
    protected $fillable = ['name', 'reward_type', 'reward_id', 'pokemon_id'];
    protected $appends = ['pokemon'];

    public function getPokemonAttribute() {
        if( !empty( $this->pokemon_id ) ) {
            $pokemon = Pokemon::find($this->pokemon_id);
            if( $pokemon ) {
                return $pokemon;
            }
        }
        return false;
    }
}
