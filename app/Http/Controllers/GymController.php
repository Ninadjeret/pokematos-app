<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\City;
use App\Models\Stop;

class GymController extends Controller {

    public function getCityGyms(City $city, Request $request){
        $gyms = Stop::where('city_id', $city->id)
            ->where('gym', 1)
            ->get();
        return response()->json($gyms, 200);
    }

}
