<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\models\City;
use App\User;

class CityController extends Controller {

    public function getAll(Request $request){
        $user = User::find(1);
        return response()->json($user->getCities(), 200);
    }

    public function getOne(Request $request, City $city){
        $user = User::find(1);
        return response()->json($city, 200);
    }
}
