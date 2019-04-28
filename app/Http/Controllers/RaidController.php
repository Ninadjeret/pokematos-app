<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\City;
use App\Models\Raid;
use App\Models\Stop;
use App\Models\Guild;
use App\Models\Announce;
use RestCord\DiscordClient;
use App\Models\raidChannel;
use Illuminate\Support\Facades\Log;

class RaidController extends Controller {

    public function getCityRaids(City $city, Request $request){
        $raids = Raid::where('city_id', $city->id)
            ->where('start_time', '>', date('Y-m-d H:i:s') )
            ->get();
        return response()->json($raids, 200);
    }

    public function create( City $city, Request $request ) {

        $gym = Stop::find($request->params['gym_id']);
        $announceType = false;

        if( $gym->getActiveRaid() || $gym->getFutureRaid() ) {
            $raid = $gym->raid;
            if( !$raid->pokemon_id && $request->params['pokemon_id'] ) {
                $raid->pokemon_id = $request->params['pokemon_id'];
                $raid->save();
                $announceType = 'raid-update';
            }
        }

        else {
            $raid = new Raid();
            $raid->city_id = $city->id;
            $raid->gym_id = $request->params['gym_id'];
            $raid->egg_level = $request->params['egg_level'];
            $raid->pokemon_id = isset( $request->params['pokemon_id'] ) ? $request->params['pokemon_id'] : null ;
            $raid->start_time = $request->params['start_time'];
            $raid->ex = (isset($request->params['ex'])) ? $request->params['ex'] : false;
            $raid->save();
            $announceType = 'raid-create';

            $guilds = Guild::where('city_id', $raid->city_id)->get();
            $gym = Stop::find($raid->gym_id);
            $startTime = new \DateTime($raid->start_time);
            if( $guilds && $raid->egg_level == 6 ) {
                $discord = new DiscordClient(['token' => config('discord.token')]);
                foreach( $guilds as $guild ) {
                    if( $guild->settings->raidsex_active && $guild->settings->raidsex_channels && $guild->settings->raidsex_channel_category_id ) {

                        $channel = $discord->guild->createGuildChannel([
                            'guild.id' => $guild->discord_id,
                            'name' => $gym->name.'-'.$startTime->format('d').'-'.$startTime->format('m'),
                            'type' => 0,
                            'parent_id' => (int) $guild->settings->raidsex_channel_category_id
                        ]);
                        raidChannel::create([
                            'raid_id' => $raid->id,
                            'guild_id' => $guild->id,
                            'channel_discord_id' => $channel->id,
                        ]);
                    }
                }
            }
        }
        if( $announceType ) {
            $announce = Announce::create([
                'type' => $announceType,
                'source' => ( !empty($request->params['type']) ) ? $request->params['type'] : 'map',
                'date' => date('Y-m-d H:i:s'),
                'user_id' => Auth::id(),
                'raid_id' => $raid->id,
            ]);
        }

        return response()->json($raid, 200);

    }

    /*public function update(City $city, Raid $raid, Request $request) {
        $raid->pokemon_id = $request->params['pokemon_id'];
        $raid->save();
        return response()->json($raid, 200);
    }*/

    public function delete(City $city, Raid $raid, Request $request) {
        Raid::destroy($raid->id);
        return response()->json(null, 204);
    }

}
