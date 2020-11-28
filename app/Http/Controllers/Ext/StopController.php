<?php

namespace App\Http\Controllers\Ext;

use App\Models\Stop;
use App\Models\Guild;
use App\Models\GuildApiLog;
use Illuminate\Http\Request;
use App\Models\GuildApiAccess;
use App\Http\Controllers\Controller;

class StopController extends Controller
{
    public function index(Request $request)
    {
        $guild_api_access = GuildApiAccess::where('key', $request->bearerToken())->first();
        $guild = Guild::find($guild_api_access->guild_id);

        $stops = Stop::where('city_id', $guild->city_id)->get()->each->setAppends(['zone', 'city', 'google_maps_url', 'aliases']);
        GuildApiLog::create([
            'api_access_id' => $guild_api_access->id,
            'endpoint' => $request->path(),
            'status' => 200,
        ]);
        return response()->json($stops, 200);
    }
}