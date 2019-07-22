<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\City;
use App\Models\Raid;
use App\Models\Stop;
use App\Models\Guild;
use App\Models\Announce;
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
        event( new \App\Events\RaidDeleted( $raid ) );
        Raid::destroy($raid->id);
        return response()->json(null, 204);
    }

}
