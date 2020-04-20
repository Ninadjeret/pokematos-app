<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\City;
use App\Models\Raid;
use App\Models\Stop;
use App\Models\Guild;
use App\Models\UserAction;
use App\Models\raidChannel;
use Illuminate\Support\Facades\Log;

class RaidController extends Controller {

    public static $feature = 'features.raid_reporting';
    public static $feature_message = 'La fonctionnalité n\'est pas active';

    public function getCityRaids(City $city, Request $request){
        $raids = Raid::where('city_id', $city->id)
            ->where('start_time', '>', date('Y-m-d H:i:s') )
            ->get();
        return response()->json($raids, 200);
    }

    public function create( City $city, Request $request ) {

        if( !config(self::$feature) )return response()->json(self::$feature_message, 403);

        $args = [];
        $args['city_id'] = $city->id;
        $args['user_id'] = Auth::id();
        if( isset( $request->params['gym_id'] ) ) $args['gym_id'] = $request->params['gym_id'];
        if( isset( $request->params['pokemon_id'] ) ) $args['pokemon_id'] = $request->params['pokemon_id'];
        if( isset( $request->params['egg_level'] ) ) $args['egg_level'] = $request->params['egg_level'];
        if( isset( $request->params['start_time'] ) ) $args['start_time'] = $request->params['start_time'];
        if( isset( $request->params['ex'] ) ) $args['ex'] = $request->params['ex'];

        $raid = Raid::add($args);
        return response()->json($raid, 200);

    }

    /*public function update(City $city, Raid $raid, Request $request) {
        $raid->pokemon_id = $request->params['pokemon_id'];
        $raid->save();
        return response()->json($raid, 200);
    }*/

    public function delete(City $city, Raid $raid, Request $request) {

        if( !config(self::$feature) )return response()->json(self::$feature_message, 403);

        $gym = Stop::find($raid->gym_id);
        $gym->touch();
        event( new \App\Events\RaidDeleted( $raid ) );
        $announces = $raid->getUserActions();
        if( !empty($announces) ) {
            foreach( $announces as $announce ) {
                UserAction::destroy($announce->id);
            }
        }
        Raid::destroy($raid->id);
        return response()->json(null, 204);
    }

    /**
     * [decodeImage description]
     * @param  Request $request [description]
     * @param  City    $city    [description]
     * @return [type]           [description]
     */
    public function addRaid( Request $request ) {

        if( !config(self::$feature) )return response()->json(self::$feature_message, 403);

        $url = ( isset($request->url) && !empty($request->url) ) ? $request->url : false ;
        $text = ( isset($request->text) && !empty($request->text) ) ? $request->text : false ;
        $username = $request->user_name;
        $userDiscordId = $request->user_discord_id;
        $guild_discord_id = $request->guild_discord_id;
        $message_discord_id = $request->message_discord_id;
        $channel_discord_id = $request->channel_discord_id;

        if( empty( $guild_discord_id ) ) {
            return response()->json('L\'ID de Guild est obligatoire', 400);
        }

        $guild = Guild::where( 'discord_id', $guild_discord_id )->first();
        $city = City::find( $guild->city->id );

        $user = User::where('discord_id', $userDiscordId)->first();
        if( !$user ) {
            $user = User::create([
                'name' => $username,
                'password' => Hash::make( str_random(20) ),
                'discord_name' => $username,
                'discord_id' => $userDiscordId,
            ]);
        }

        if( $url ) {
            $imageAnalyzer = new ImageAnalyzer($url, $guild, $user, $channel_discord_id);
            $result = $imageAnalyzer->result;
            $source_type = 'image';
        } else {
            $textAnalyzer = new TextAnalyzer($text, $guild, $user, $channel_discord_id);
            $result = $textAnalyzer->result;
            $source_type = 'text';
        }

        if( empty( $result->error ) && $result->eggLevel > 0 ) {
            $args = [];
            $args['city_id'] = $city->id;
            $args['user_id'] = $user->id;
            $args['gym_id'] = $result->gym->id;
            $args['message_discord_id'] = $message_discord_id;
            $args['channel_discord_id'] = $channel_discord_id;
            $args['guild_id'] = $guild->id;
            $args['source_type'] = $source_type;
            if( isset( $result->pokemon->id ) ) $args['pokemon_id'] = $result->pokemon->id;
            if( isset( $result->eggLevel ) ) {
                $args['egg_level'] = $result->eggLevel;
                if( $result->eggLevel == '6' ) {
                    $args['ex'] = true;
                }
            }
            if( isset( $result->date ) ) $args['start_time'] = $result->date;

            $raid = Raid::add($args);
            return response()->json($raid, 200);
        }

        return response()->json($result, 400);

    }

    /**
     * [decodeImage description]
     * @param  Request $request [description]
     * @param  City    $city    [description]
     * @return [type]           [description]
     */
    public function imageDecode( Request $request ) {

        if( !config(self::$feature) )return response()->json(self::$feature_message, 403);

        $url = ( isset($request->url) && !empty($request->url) ) ? $request->url : false ;
        $guild_discord_id = $request->guild_discord_id;

        if( empty( $guild_discord_id ) ) {
            return response()->json('L\'ID de Guild est obligatoire', 400);
        }

        $guild = Guild::where( 'discord_id', $guild_discord_id )->first();
        $city = City::find( $guild->city->id );

        if( $url ) {
            $imageAnalyzer = new ImageAnalyzer($url, $guild);
            $result = $imageAnalyzer->result;
            return response()->json($result, 200);
        } else {
            return response()->json('URL de l\'image obligatoire', 400);
        }
    }

}
