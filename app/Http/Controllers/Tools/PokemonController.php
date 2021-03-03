<?php

namespace App\Http\Controllers\Tools;

use App\Models\Pokemon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PokemonController extends Controller
{
    public function getPokedexIdFromNameFr(Request $request, $name)
    {
        $pokemon = Pokemon::where('name_fr', $name)->first();
        if ($pokemon) {
            $id = $pokemon->pokedex_id;
            $id = ltrim($id, '0');
            return $id;
        }
        return response()->json('Pokémon non trouvé', 404);
    }
}
