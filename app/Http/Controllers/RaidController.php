<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\City;
use App\Models\Raid;

class RaidController extends Controller {

    public function getCityRaids(City $city, Request $request){
        $raids = Raid::where('city_id', $city->id)
            ->where('start_time', '>', date('Y-m-d H:i:s') )
            ->get();
        return response()->json($raids, 200);
    }

}
