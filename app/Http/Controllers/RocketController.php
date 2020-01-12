<?php

namespace App\Http\Controllers;

use App\Models\Stop;
use App\Models\City;
use App\Models\UserAction;
use App\Models\RocketBoss;
use Illuminate\Http\Request;
use App\Models\RocketInvasion;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

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

        $user = Auth::user();

        $stop = Stop::find($request->params['stop_id']);
        if( empty($stop) ) {
            return response()->json('Stop non trouvé', 404);
        }

        if( $stop->invasion ) {
            $announce = UserAction::create([
                'type' => 'rocket-invasion-duplicate',
                'source' => 'map',
                'date' => date('Y-m-d H:i:s'),
                'user_id' => $user->id,
                'relation_id' => $invasion->id,
                'message_discord_id' => null,
                'channel_discord_id' => null,
                'guild_id' => null,
            ]);
            return response()->json($stop->invasion, 200);
        }

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

        $announce = UserAction::create([
            'type' => 'rocket-invasion-create',
            'source' => 'map',
            'date' => date('Y-m-d H:i:s'),
            'user_id' => $user->id,
            'relation_id' => $invasion->id,
            'message_discord_id' => null,
            'channel_discord_id' => null,
            'guild_id' => null,
        ]);

        event( new \App\Events\RocketInvasionCreated( $invasion, $announce ) );

        return response()->json($invasion, 200);
    }

    public function updateInvasion( City $city, RocketInvasion $invasion, Request $request ) {

        $user = Auth::user();

        $args = [
            'city_id' => $city->id,
            'stop_id' => $request->params['stop_id'],
            'date' => date('Y-m-d'),
            'boss_id' => $request->params['boss_id'],
            'pokemon_step1' => (isset($request->params['pokemon_step1'])) ? $request->params['pokemon_step1'] : null,
            'pokemon_step2' => (isset($request->params['pokemon_step2'])) ? $request->params['pokemon_step2'] : null,
            'pokemon_step3' => (isset($request->params['pokemon_step3'])) ? $request->params['pokemon_step3'] : null,
        ];
        $invasion->update($args);

        $announce = UserAction::create([
            'type' => 'rocket-invasion-update',
            'source' => 'map',
            'date' => date('Y-m-d H:i:s'),
            'user_id' => $user->id,
            'relation_id' => $invasion->id,
            'message_discord_id' => null,
            'channel_discord_id' => null,
            'guild_id' => null,
        ]);

        event( new \App\Events\RocketInvasionUpdated( $invasion, $announce ) );

        return response()->json($invasion, 200);
    }

    public function deleteInvasion( City $city, RocketInvasion $invasion, Request $request ) {
        $user = Auth::user();

        $announces = $invasion->getUserActions();
        if( !empty($announces) ) {
            foreach( $announces as $announce ) {
                UserAction::destroy($announce->id);
            }
        }

        $stop = Stop::find($invasion->stop_id);
        $stop->touch();
        RocketInvasion::destroy($invasion->id);
        return response()->json(null, 204);
    }
}
