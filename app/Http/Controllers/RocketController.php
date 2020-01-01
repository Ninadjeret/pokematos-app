<?php

namespace App\Http\Controllers;

use App\Models\Stop;
use App\Models\City;
use App\Models\RocketBoss;
use Illuminate\Http\Request;
use App\Models\RocketInvasion;

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


    public function createInvasion( City $city, Request $request ) {
        $args = [
            'city_id' => $city->id,
            'stop_id' => $request->params['stop_id'],
            'date' => date('Y-m-d'),
            'boss_id' => $request->params['boss_id'],
            'pokemon_step1' => (isset($request->params['pokemon_step1'])) ? $request->params['pokemon_step1'] : null,
            'pokemon_step2' => (isset($request->params['pokemon_step2'])) ? $request->params['pokemon_step2'] : null,
            'pokemon_step3' => (isset($request->params['pokemon_step3'])) ? $request->params['pokemon_step3'] : null,
        ];
        $invasion = RocketInvasion::create($args);
        return response()->json($invasion, 200);
    }

    public function updateInvasion( City $city, RocketInvasion $invasion, Request $request ) {
        $args = [
            'city_id' => $city->id,
            'stop_id' => $request->params['stop_id'],
            'date' => date('Y-m-d'),
            'boss_id' => $request->params['boss_id'],
            'pokemon_step1' => (isset($request->params['pokemon_step1'])) ? $request->params['pokemon_step1'] : null,
            'pokemon_step2' => (isset($request->params['pokemon_step2'])) ? $request->params['pokemon_step2'] : null,
            'pokemon_step3' => (isset($request->params['pokemon_step3'])) ? $request->params['pokemon_step3'] : null,
        ];
        $invasion = $invasion->update($args);
        return response()->json($invasion, 200);
    }

    public function deleteInvasion( City $city, RocketInvasion $invasion, Request $request ) {
        $stop = Stop::find($invasion->stop_id);
        $stop->touch();
        RocketInvasion::destroy($invasion->id);
        return response()->json(null, 204);
    }
}
