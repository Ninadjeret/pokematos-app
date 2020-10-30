<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\City;
use App\Models\Raid;
use App\Models\Stop;
use App\Models\Guild;
use App\Models\UserAction;
use App\Models\raidChannel;
use Illuminate\Http\Request;
use App\Core\Analyzer\TextAnalyzer;
use App\Core\Analyzer\ImageAnalyzer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RaidController extends Controller
{

    public static $feature = 'features.raid_reporting';
    public static $feature_message = 'La fonctionnalité n\'est pas active';





    /*public function update(City $city, Raid $raid, Request $request) {
        $raid->pokemon_id = $request->params['pokemon_id'];
        $raid->save();
        return response()->json($raid, 200);
    }*/



    /**
     * [decodeImage description]
     * @param  Request $request [description]
     * @param  City    $city    [description]
     * @return [type]           [description]
     */


    /**
     * [decodeImage description]
     * @param  Request $request [description]
     * @param  City    $city    [description]
     * @return [type]           [description]
     */
    public function imageDecode(Request $request)
    {

        if (!config(self::$feature)) return response()->json(self::$feature_message, 403);

        $url = (isset($request->url) && !empty($request->url)) ? $request->url : false;
        $guild_discord_id = $request->guild_discord_id;

        if (empty($guild_discord_id)) {
            return response()->json('L\'ID de Guild est obligatoire', 400);
        }

        $guild = Guild::where('discord_id', $guild_discord_id)->first();
        $city = City::find($guild->city->id);

        if ($url) {
            $imageAnalyzer = new ImageAnalyzer($url, $guild);
            if (empty($imageAnalyzer->result->error)) {
                $imageAnalyzer->run();
                $result = $imageAnalyzer->result;
                return response()->json($result, 200);
            }
            return response()->json('image_failed', 400);
        } else {
            return response()->json('URL de l\'image obligatoire', 400);
        }
    }
}
