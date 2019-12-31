<?php

namespace App\Http\Controllers;

use App\Models\RocketBoss;
use Illuminate\Http\Request;

class RocketController extends Controller
{

    /**
     *
     */
    public function getBosses( Request $request ) {
        $bosses = RocketBoss::orderBy('id', 'asc')->get();
        return response()->json($bosses, 200);
    }


    /**
     *
     */
    public function updateBoss( Request $request, RocketBoss $boss) {

        $args = [];

        if( !empty($request->pokemon) ) {
            foreach( $request->pokemon as $level => $pokemons ) {
                $to_save = [];
                $to_save_name = 'pokemon_'.$level;
                if( !empty($pokemons) ) {
                    foreach( $pokemons as $pokemon ) {
                        $to_save[] = $pokemon['id'];
                    }
                }
                $args[$to_save_name] = $to_save;
            }
        }

        if( !empty($args) ) $boss->update($args);

        return response()->json($boss, 200);
    }
}
