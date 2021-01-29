<?php

namespace App\Http\Controllers\Ext;

use App\Models\City;
use App\Models\Guild;
use App\Models\GuildApiLog;
use Illuminate\Http\Request;
use App\Models\GuildApiAccess;
use App\Core\Rankings\UserRanking;
use App\Http\Controllers\Controller;

class RankingController extends Controller
{
    public function index(Request $request)
    {
        $guild_api_access = \App\Core\Ext\Auth::access();
        $guild = Guild::find($guild_api_access->guild_id);
        $city = City::find($guild->city_id);

        $start = (!empty($request->start)) ? $request->start : '2010-01-01';
        $end = (!empty($request->end)) ? $request->end : date('Y-m-d');

        $rankings = [
            'raids' => UserRanking::forRaids()
                ->forCity($city)
                ->forPeriod($start, $end)
                ->getComplete(),
            'quests' => UserRanking::forQuests()
                ->forCity($city)
                ->forPeriod($start, $end)
                ->getComplete(),
            'rocket' => UserRanking::forRocket()
                ->forCity($city)
                ->forPeriod($start, $end)
                ->getComplete(),
            'top100' => UserRanking::forAll()
                ->setLimit(100)
                ->forPeriod($start, $end)
                ->getComplete(),
        ];
        GuildApiLog::create([
            'api_access_id' => $guild_api_access->id,
            'endpoint' => $request->path(),
            'status' => 200,
        ]);
        return response()->json($rankings, 200);
    }

    public function show(Request $request, $ranking)
    {
        $guild_api_access = \App\Core\Ext\Auth::access();
        $guild = Guild::find($guild_api_access->guild_id);
        $city = City::find($guild->city_id);

        $start = (!empty($request->start)) ? $request->start : '2010-01-01';
        $end = (!empty($request->end)) ? $request->end : date('Y-m-d');

        if ($ranking == 'raids') {
            $result = UserRanking::forRaids()->forCity($city)->forPeriod($start, $end)->getComplete();
        } elseif ($ranking == 'top100') {
            $result = UserRanking::forAll()->setLimit(100)->forPeriod($start, $end)->getComplete();
        } elseif ($ranking == 'quests') {
            $result = UserRanking::forQuests()->forCity($city)->forPeriod($start, $end)->getComplete();
        } else {
            $result = UserRanking::forRocket()->forCity($city)->forPeriod($start, $end)->getComplete();
        }
        GuildApiLog::create([
            'api_access_id' => $guild_api_access->id,
            'endpoint' => $request->path(),
            'status' => 200,
        ]);
        return response()->json($result, 200);
    }
}