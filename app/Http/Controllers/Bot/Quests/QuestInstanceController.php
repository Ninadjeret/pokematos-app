<?php

namespace App\Http\Controllers\Bot\Quests;

use App\User;
use App\Models\City;
use App\Models\Guild;
use App\Models\QuestInstance;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class QuestInstanceController extends Controller
{
    public function store(Request $request)
    {
        $url = (isset($request->url) && !empty($request->url)) ? $request->url : false;
        $text = (isset($request->text) && !empty($request->text)) ? $request->text : false;
        $guild_discord_id = $request->guild_discord_id;
        $message_discord_id = $request->message_discord_id;
        $channel_discord_id = $request->channel_discord_id;

        $guild = Guild::where('discord_id', $guild_discord_id)->first();
        $city = City::find($guild->city->id);
        $user = User::initFromBotRequest($request);

        $instance = new \App\Core\Analyzer\Image\Quest([
            'source_url' => $url,
            'source_text' => $text,
            'guild' => $guild,
            'user' => $user,
            'channel_discord_id' => $channel_discord_id,
        ]);

        $instance->perform();
        if (empty($instance->result->error)) {
            $result = $instance->result;
        } else {
            return response()->json('', 400);
        }

        $args = [
            'city_id' => $city->id,
            'gym_id' => $result->gym->id,
            'reward_type' => $result->reward->type,
            'reward_id' => $result->reward->id,
            'type' => 'img',
        ];

        $raid = QuestInstance::add($args);
        return response()->json($raid, 200);
    }
}
