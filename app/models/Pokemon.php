<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pokemon extends Model {

    protected $fillable = ['boss', 'boss_level'];
    protected $table = 'pokemons';
    protected $appends = ['thumbnail_url'];
    protected $casts = [
        'boss' => 'boolean',
        'shiny' => 'boolean',
    ];

    public function getThumbnailUrlAttribute() {
        return 'https://assets.profchen.fr/img/pokemon/pokemon_icon_'.$this->pokedex_id.'_'.$this->form_id.'.png';
    }

    public function getCp( $lvl, $sta, $att, $def ) {

    }

}
