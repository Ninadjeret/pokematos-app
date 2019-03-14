<?php

namespace App\Http\Controllers;

use App\Models\Pokemon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

    public function updateRaidBosses(Request $request) {
        $pokemon_ids = [];
        $bosses_1t = $request->bosses1t;
        if( $bosses_1t ) {
            foreach( $bosses_1t as $boss ) {
                $pokemon = Pokemon::find($boss['id']);
                if( $pokemon ) {
                    $pokemon->update([
                        'boss' => 1,
                        'boss_level' => 1
                    ]);
                    $pokemon_ids[] = $boss['id'];
                }
            }
        }

        $bosses_2t = $request->bosses2t;
        if( $bosses_2t ) {
            foreach( $bosses_2t as $boss ) {
                $pokemon = Pokemon::find($boss['id']);
                if( $pokemon ) {
                    $pokemon->update([
                        'boss' => 1,
                        'boss_level' => 2
                    ]);
                    $pokemon_ids[] = $boss['id'];
                }
            }
        }

        $bosses_3t = $request->bosses3t;
        if( $bosses_3t ) {
            foreach( $bosses_3t as $boss ) {
                $pokemon = Pokemon::find($boss['id']);
                if( $pokemon ) {
                    $pokemon->update([
                        'boss' => 1,
                        'boss_level' => 3
                    ]);
                    $pokemon_ids[] = $boss['id'];
                }
            }
        }

        $bosses_4t = $request->bosses4t;
        if( $bosses_4t ) {
            foreach( $bosses_4t as $boss ) {
                $pokemon = Pokemon::find($boss['id']);
                if( $pokemon ) {
                    $pokemon->update([
                        'boss' => 1,
                        'boss_level' => 4
                    ]);
                    $pokemon_ids[] = $boss['id'];
                }
            }
        }

        $bosses_5t = $request->bosses5t;
        if( $bosses_5t ) {
            foreach( $bosses_5t as $boss ) {
                Log::debug( '$boss' );
                Log::debug( print_r($boss, true) );
                $pokemon = Pokemon::find($boss['id']);
                if( $pokemon ) {
                    $pokemon->update([
                        'boss' => 1,
                        'boss_level' => 5
                    ]);
                    $pokemon_ids[] = $boss['id'];
                }
            }
        }


        Log::debug( print_r($pokemon_ids, true) );

        $old_bosses = Pokemon::whereNotIn('id', $pokemon_ids)->get();
        if( !empty($old_bosses) ) {
            foreach( $old_bosses as $old_boss ) {
                $old_boss->update([
                    'boss' => 0,
                    'boss_level' => null
                ]);
            }
        }

        //Return
        $pokemons = Pokemon::where('boss', 1)
            ->get();
        return response()->json($pokemons, 200);
    }

}
