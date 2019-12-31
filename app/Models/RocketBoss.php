<?php

namespace App\Models;

use App\Models\Pokemon;
use Illuminate\Database\Eloquent\Model;

class RocketBoss extends Model
{
    protected $table = 'rocket_bosses';
    protected $fillable = [
        'name',
        'level',
        'pokemon_step1',
        'pokemon_step2',
        'pokemon_step3',
    ];
    protected $casts = [
        'pokemon_step1' => 'array',
        'pokemon_step2' => 'array',
        'pokemon_step3' => 'array',
    ];
    protected $appends = ['pokemon'];
    protected $hidden = ['pokemon_step1', 'pokemon_step2', 'pokemon_step3'];

    public function getPokemonAttribute() {
        $return = [];
        $levels = ['pokemon_step1', 'pokemon_step2', 'pokemon_step3'];

        foreach( $levels as $level ) {
            $step = str_replace('pokemon_', '', $level);
            $return[$step] = [];
            $pokemon = $this->$level;
            if( !empty($pokemon) ) {
                foreach( $pokemon as $pokemon_id ) {
                    $return[$level][] = Pokemon::find($pokemon_id); 
                }
            }
        }

        return $return;
    }
}
