<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pokemon extends Model {

    protected $fillable = [ 'pokedex_id', 'niantic_id', 'name_fr', 'name_ocr', 'base_att', 'base_def', 'base_sta', 'parent_id', 'boss', 'boss_level'];
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
