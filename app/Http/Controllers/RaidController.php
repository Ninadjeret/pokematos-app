<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\City;
use App\Models\Raid;
use App\Models\Stop;

class RaidController extends Controller {

    public function getCityRaids(City $city, Request $request){
        $raids = Raid::where('city_id', $city->id)
            ->where('start_time', '>', date('Y-m-d H:i:s') )
            ->get();
        return response()->json($raids, 200);
    }

    public function create( City $city, Request $request ) {
        $gym = Stop::find($request->params['gym_id']);

        if( $gym->getActiveRaid() ) {

        }

        elseif( $gym->getFutureRaid() ) {

        }

        else {
            $raid = new Raid();
            $raid->city_id = $city->id;
            $raid->gym_id = $request->params['gym_id'];
            $raid->egg_level = $request->params['egg_level'];
            $raid->pokemon_id = $request->params['pokemon_id'];
            $raid->start_time = $request->params['start_time'];
            $raid->save();
            return response()->json($raid, 200);
        }

        return response()->json($gym->getFutureRaid(), 200);
    }

}
