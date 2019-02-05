<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pokemon;

class PokemonController extends Controller {

    public function getAll(Request $request){
        $pokemons = Pokemon::all();
        return response()->json($pokemons, 200);
    }

    public function getRaidBosses(Request $request){
        $pokemons = Pokemon::where('boss', 1)
            ->get();
        return response()->json($pokemons, 200);
    }

}
