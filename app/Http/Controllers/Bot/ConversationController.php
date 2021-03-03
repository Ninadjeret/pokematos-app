<?php

namespace App\Http\Controllers\Bot;

use App\User;
use App\Models\Guild;
use App\Models\UserAction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class ConversationController extends Controller
{
    public function store(Request $request)
    {
        $message = (isset($request->message) && !empty($request->message)) ? $request->message : false;
        $username = $request->user_discord_name;
        $user_discord_id = $request->user_discord_id;
        $guild_discord_id = $request->guild_discord_id;
        $message_discord_id = $request->message_discord_id;
        $channel_discord_id = $request->channel_discord_id;

        $guild = Guild::where('discord_id', $guild_discord_id)->first();

        $user = User::where('discord_id', $user_discord_id)->first();
        if (!$user) {
            $user = User::create([
                'name' => $username,
                'password' => Hash::make(str_random(20)),
                'discord_name' => $username,
                'discord_id' => $user_discord_id,
            ]);
        }

        $user_action = UserAction::create([
            'type' => 'conversation',
            'source' => 'discord',
            'date' => date('Y-m-d H:i:s'),
            'content' => $message,
            'user_id' => $user->id,
            'message_discord_id' => $message_discord_id,
            'channel_discord_id' => $channel_discord_id,
            'guild_id' => $guild->id,
        ]);

        $reply = $user_action->reply();

        return response()->json($reply, 200);
    }
}
