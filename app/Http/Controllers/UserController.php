<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\City;
use App\Models\Guild;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller {

    public function getUSer() {
        $user = Auth::user();
        return response()->json($user, 200);
    }

    public function getCities() {
        $user = Auth::user();
        return response()->json($user->getCities(), 200);
    }

    public static function getGuildOptions( Request $request, City $city, Guild $guild ) {
        return response()->json($guild->settings, 200);
    }

    public static function updateGuildOptions( Request $request, City $city, Guild $guild ) {
        $settings = $request->settings;
        $guild->updateSettings($settings);
        return response()->json($guild, 200);
    }

}
