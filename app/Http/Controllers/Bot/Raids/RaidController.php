<?php

namespace App\Http\Controllers\Bot\Raids;

use App\User;
use App\Models\City;
use App\Models\Raid;
use App\Models\Guild;
use Illuminate\Http\Request;
use App\Core\Analyzer\TextAnalyzer;
use App\Core\Analyzer\ImageAnalyzer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

class RaidController extends Controller
{

    public static $feature = 'features.raid_reporting';
    public static $feature_message = 'La fonctionnalité n\'est pas active';

    public function store(Request $request)
    {
        if (!config(self::$feature)) return response()->json(self::$feature_message, 403);

        $url = (isset($request->url) && !empty($request->url)) ? $request->url : false;
        $text = (isset($request->text) && !empty($request->text)) ? $request->text : false;
        $username = $request->user_name;
        $userDiscordId = $request->user_discord_id;
        $guild_discord_id = $request->guild_discord_id;
        $message_discord_id = $request->message_discord_id;
        $channel_discord_id = $request->channel_discord_id;

        if (empty($guild_discord_id)) {
            return response()->json('L\'ID de Guild est obligatoire', 400);
        }

        $guild = Guild::where('discord_id', $guild_discord_id)->first();
        $city = City::find($guild->city->id);

        $user = User::where('discord_id', $userDiscordId)->first();
        if (!$user) {
            $user = User::create([
                'name' => $username,
                'password' => Hash::make(str_random(20)),
                'discord_name' => $username,
                'discord_id' => $userDiscordId,
            ]);
        }

        if ($url) {
            //$imageAnalyzer = new ImageAnalyzer($url, $guild, $user, $channel_discord_id);
            $instance = new \App\Core\Analyzer\Image\Raid([
                'source_url' => $url,
                'guild' => $guild,
                'user' => $user,
                'channel_discord_id' => $channel_discord_id,
            ]);
            $instance->perform();
            if (empty($instance->result->error)) {
                $result = $instance->result;
                $source_type = 'image';
            } else {
                return false;
            }
        } else {
            //$textAnalyzer = new TextAnalyzer($text, $guild, $user, $channel_discord_id);
            $instance = new \App\Core\Analyzer\Text\Raid([
                'source_text' => $text,
                'guild' => $guild,
                'user' => $user,
                'channel_discord_id' => $channel_discord_id,
            ]);
            $instance->perform();
            $result = $instance->result;
            $source_type = 'text';
        }

        if (empty($result->error) && $result->eggLevel > 0) {
            $args = [];
            $args['city_id'] = $city->id;
            $args['user_id'] = $user->id;
            $args['gym_id'] = $result->gym->id;
            $args['message_discord_id'] = $message_discord_id;
            $args['channel_discord_id'] = $channel_discord_id;
            $args['guild_id'] = $guild->id;
            $args['source_type'] = $source_type;
            if (isset($result->pokemon->id)) $args['pokemon_id'] = $result->pokemon->id;
            if (isset($result->eggLevel)) {
                $args['egg_level'] = $result->eggLevel;
                if ($result->eggLevel == '6') {
                    $args['ex'] = true;
                }
            }
            if (isset($result->date)) $args['start_time'] = $result->date;

            $raid = Raid::add($args);
            return response()->json($raid, 200);
        }

        return response()->json($result, 400);
    }
}