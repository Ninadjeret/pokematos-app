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
        $bosses = RocketBoss::all();
        return response()->json($bosses, 200); 
    }


    /**
     * 
     */
    public function updateBoss( Request $request, RocketBoss $boss) {

        $args = [];

        if( !empty($request->pokemon) ) {
            foreach( $request->pokemon as $level ) {
                $to_save = [];
                $to_save_name = 'pokemon_'.$level;
                if( !empty($level) ) {
                    foreach( $level as $pokemon ) {
                        $to_save[] = $pokemon->id;
                    }
                }
                $args[$to_save_name] = $to_save;
            }
        }

        if( !empty($args) ) $boss->update($args);

        return response()->json($boss, 200); 
    }
}
