<?php

namespace App\Http\Controllers;
use App\Models\Stop;
use App\Models\City;
use App\Models\Guild;
use App\Helpers\Helpers;
use App\Models\UserAction;
use App\Models\RocketBoss;
use Illuminate\Http\Request;
use App\Models\RocketInvasion;
use App\Models\RocketConnector;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class RocketController extends Controller
{

    public static $feature = 'features.rocket';
    public static $feature_message = 'La fonctionnalité n\'est pas active';

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

        if( !config(self::$feature) )return response()->json(self::$feature_message, 403);

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
                'relation_id' => $stop->invasion->id,
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

        $stop->touch();

        event( new \App\Events\RocketInvasionCreated( $invasion, $announce ) );

        return response()->json($invasion, 200);
    }

    public function updateInvasion( City $city, RocketInvasion $invasion, Request $request ) {

        if( !config(self::$feature) )return response()->json(self::$feature_message, 403);

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

        $invasion->getStop()->touch();

        event( new \App\Events\RocketInvasionUpdated( $invasion, $announce ) );

        return response()->json($invasion, 200);
    }

    public function deleteInvasion( City $city, RocketInvasion $invasion, Request $request ) {

        if( !config(self::$feature) )return response()->json(self::$feature_message, 403);
        $user = Auth::user();

        $announces = $invasion->getUserActions();
        if( !empty($announces) ) {
            foreach( $announces as $announce ) {
                UserAction::destroy($announce->id);
            }
        }

        $stop = Stop::find($invasion->stop_id);
        $stop->touch();
        event( new \App\Events\RocketInvasionDeleted( $invasion ) );
        $announces = $invasion->getUserActions();
        if( !empty($announces) ) {
            foreach( $announces as $announce ) {
                UserAction::destroy($announce->id);
            }
        }
        RocketInvasion::destroy($invasion->id);
        return response()->json(null, 204);
    }


    /**
     * [getConnectors description]
     * @param  Guild   $guild   [description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function getConnectors( Guild $guild, Request $request ) {
        $user = Auth::user();
        if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
        $connectors = RocketConnector::where('guild_id', $guild->id)->get();
        return response()->json($connectors, 200);
    }

    /**
     * [getConnectors description]
     * @param  Guild   $guild   [description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function createConnector( Guild $guild, Request $request ) {
        $user = Auth::user();
        if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
        $connector = RocketConnector::create([
            'name' => ( isset( $request->name ) ) ? $request->name : '' ,
            'guild_id' => $guild->id,
            'channel_discord_id' => ( isset( $request->channel_discord_id ) ) ? $request->channel_discord_id : '' ,
            'filter_stop_type' => ( isset( $request->filter_stop_type ) ) ? $request->filter_stop_type : 'none' ,
            'filter_stop_zone' => ( isset( $request->filter_stop_zone ) ) ? Helpers::extractIds($request->filter_stop_zone) : '' ,
            'filter_stop_gym' => ( isset( $request->filter_stop_stop ) ) ? Helpers::extractIds($request->filter_stop_stop) : '' ,
            'filter_boss_type' => ( isset( $request->filter_boss_type ) ) ? $request->filter_boss_type : '' ,
            'filter_boss_bosses' => ( isset( $request->filter_boss_bosses ) ) ? Helpers::extractIds($request->filter_boss_bosses) : '' ,
            'format' => ( isset( $request->format ) ) ? $request->format : 'auto' ,
            'custom_message' => ( isset( $request->custom_message ) ) ? $request->custom_message : '' ,
            'delete_after_end' => ( isset( $request->delete_after_end ) ) ? $request->delete_after_end : '' ,
        ]);
        return response()->json($connector, 200);
    }

    /**
     * [getConnector description]
     * @param  Request         $request   [description]
     * @param  Guild           $guild     [description]
     * @param  RocketConnector $connector [description]
     * @return [type]                     [description]
     */
    public function getConnector( Request $request, Guild $guild, RocketConnector $connector ) {
        $user = Auth::user();
        if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
        return response()->json($connector, 200);
    }

    public function updateConnector( Guild $guild, RocketConnector $connector, Request $request ) {
        $user = Auth::user();
        if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
        $connector->update([
            'name' => ( isset( $request->name ) ) ? $request->name : $connector->name ,
            'guild_id' => $guild->id,
            'channel_discord_id' => ( isset( $request->channel_discord_id ) ) ? $request->channel_discord_id : $connector->channel_discord_id ,
            'filter_stop_type' => ( isset( $request->filter_stop_type ) ) ? $request->filter_stop_type : $connector->filter_stop_type ,
            'filter_stop_zone' => ( isset( $request->filter_stop_zone ) ) ? Helpers::extractIds($request->filter_stop_zone) : $connector->filter_stop_zone ,
            'filter_stop_gym' => ( isset( $request->filter_stop_stop ) ) ? Helpers::extractIds($request->filter_stop_stop) : $connector->filter_stop_stop ,
            'filter_boss_type' => ( isset( $request->filter_boss_type ) ) ? $request->filter_boss_type : $connector->filter_boss_type ,
            'filter_boss_bosses' => ( isset( $request->filter_boss_bosses ) ) ? Helpers::extractIds($request->filter_boss_bosses) : Helpers::extractIds($connector->filter_boss_bosses) ,
            'format' => ( isset( $request->format ) ) ? $request->format : $connector->format ,
            'custom_message' => ( isset( $request->custom_message ) ) ? $request->custom_message : $connector->custom_message ,
            'delete_after_end' => ( isset( $request->delete_after_end ) ) ? $request->delete_after_end : $connector->delete_after_end ,
        ]);
        return response()->json($connector, 200);
    }

    public function deleteConnector(Guild $guild, RocketConnector $connector, Request $request ) {
        $user = Auth::user();
        if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
        RocketConnector::destroy($connector->id);
        return response()->json(null, 204);
    }

}
