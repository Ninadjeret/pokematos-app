<?php

namespace App\Http\Controllers\Tools;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RessourcesController extends Controller
{
    public function florkedPokemon($name)
    {
        $pokemon = \App\Models\Pokemon::where('name_fr', $name)->first();
        if ($pokemon) {
            $id = $pokemon->pokedex_id;
            $id = ltrim($id, '0');

            $form = \App\Core\Helpers::getPokemonFormFromName($name);
            if ($form) $id .= $form['florked'];

            $remoteImage = 'https://gamepress.gg/pokemongo/sites/pokemongo/files/styles/240w/public/flork-images/' . $id . '.png';
            $imginfo = getimagesize($remoteImage);
            header("Content-type: {$imginfo['mime']}");
            readfile($remoteImage);
        }
    }

    public function nianticPokemon($name)
    {
        $pokemon = \App\Models\Pokemon::where('name_fr', $name)->first();
        if ($pokemon) {
            $id = $pokemon->pokedex_id;

            $form = \App\Core\Helpers::getPokemonFormFromName($name);
            if ($form) {
                $id .= '_' . $form['id'];
            } else {
                $id .= '_' . $pokemon->form_id;
            }

            $remoteImage = 'https://assets.profchen.fr/img/pokemon/pokemon_icon_' . $id . '.png';
            $imginfo = getimagesize($remoteImage);
            header("Content-type: {$imginfo['mime']}");
            readfile($remoteImage);
        }
    }

    public function shufflePokemon($name)
    {
        $pokemon = \App\Models\Pokemon::where('name_fr', $name)->first();
        if ($pokemon) {
            $id = $pokemon->pokedex_id;
            $id = ltrim($id, '0');

            $form = \App\Core\Helpers::getPokemonFormFromName($name);
            if ($form) {
                $id .= '-' . $form['shuffle'];
            }

            $remoteImage = 'https://assets.profchen.fr/img/pokemon/shuffle/' . $id . '.png';
            $imginfo = getimagesize($remoteImage);
            header("Content-type: {$imginfo['mime']}");
            ob_clean();
            flush();
            readfile($remoteImage);
        }
    }
}