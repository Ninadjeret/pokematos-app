<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\City;
use App\Models\Raid;
use App\Models\Stop;
use App\Models\Announce;

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
