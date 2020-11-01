<?php

namespace App\Http\Controllers\App\Pokemon;

use App\Models\Pokemon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PokemonController extends Controller
{
    public function index(Request $request)
    {
        $pokemon = Pokemon::all();
        return response()->json($pokemon, 200);
    }

    public function update(Request $request, Pokemon $pokemon)
    {
        $args = $request->all();
        $pokemon->update($args);
        return response()->json($pokemon, 200);
    }

    public function show(Request $request, Pokemon $pokemon)
    {
        return response()->json($pokemon, 200);
    }

    public function destroy(Request $request, Pokemon $pokemon)
    {
        Pokemon::destroy($pokemon->id);
        return response()->json(null, 204);
    }
}