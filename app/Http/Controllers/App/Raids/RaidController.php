<?php

namespace App\Http\Controllers\App\Raids;

use App\Models\Stop;
use App\Models\City;
use App\Models\Raid;
use App\Models\UserAction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class RaidController extends Controller
{

    public static $feature = 'features.raid_reporting';
    public static $feature_message = 'La fonctionnalité n\'est pas active';

    public function index(City $city, Request $request)
    {
        $raids = Raid::where('city_id', $city->id)
            ->where('start_time', '>', date('Y-m-d H:i:s'))
            ->get();
        return response()->json($raids, 200);
    }

    public function store(City $city, Request $request)
    {

        if (!config(self::$feature)) return response()->json(self::$feature_message, 403);
        $args['city_id'] = $city->id;
        $args['user_id'] = Auth::id();
        if (isset($request->params['gym_id'])) $args['gym_id'] = $request->params['gym_id'];
        if (isset($request->params['pokemon_id'])) $args['pokemon_id'] = $request->params['pokemon_id'];
        if (isset($request->params['egg_level'])) $args['egg_level'] = $request->params['egg_level'];
        if (isset($request->params['start_time'])) $args['start_time'] = $request->params['start_time'];
        if (isset($request->params['ex'])) $args['ex'] = $request->params['ex'];

        $raid = Raid::add($args);
        return response()->json($raid, 200);
    }

    public function destroy(City $city, Raid $raid, Request $request)
    {

        if (!config(self::$feature)) return response()->json(self::$feature_message, 403);

        $gym = Stop::find($raid->gym_id);
        $gym->touch();
        event(new \App\Events\RaidDeleted($raid));
        $announces = $raid->getUserActions();
        if (!empty($announces)) {
            foreach ($announces as $announce) {
                UserAction::destroy($announce->id);
            }
        }
        Raid::destroy($raid->id);
        return response()->json(null, 204);
    }
}