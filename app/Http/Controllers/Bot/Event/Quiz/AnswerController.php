<?php

namespace App\Http\Controllers\Bot\Event\Quiz;

use App\Models\Guild;
use App\Models\Event;
use App\Core\Conversation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class AnswerController extends Controller
{
    public function store(Request $request)
    {
        $guild = Guild::where('discord_id', $request->guild_discord_id)->first();
        $event = Event::findFromChannelId($request->channel_discord_id);

        $username = $request->user_discord_name;
        $userDiscordId = $request->user_discord_id;
        $user = \App\User::where('discord_id', $userDiscordId)->first();
        if (!$user) {
            $user = \App\User::create([
                'name' => $username,
                'password' => Hash::make(str_random(20)),
                'discord_name' => $username,
                'discord_id' => $userDiscordId,
            ]);
        }

        if (empty($event)) {
            Conversation::sendToDiscord($request->channel_discord_id, $guild, 'bot', 'cmd_no_current_event');
            return response()->json('cmd_no_current_event', 400);
        }
        if ($event->type != 'quiz') {
            Conversation::sendToDiscord($request->channel_discord_id, $guild, 'bot', 'cmd_event_not_quiz');
            return response()->json('cmd_event_not_quiz', 400);
        }

        $args = [
            'answer' => $request->answer,
            'user_id' => $user->id,
            'guild_discord_id' => $request->guild_discord_id,
            'message_discord_id' => $request->message_discord_id,
        ];

        $event->quiz->addAnswer($args);
        return response()->json(null, 204);
    }
}
