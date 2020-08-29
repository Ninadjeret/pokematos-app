<?php

namespace App\Http\Controllers\Bot\Event\Train;

use App\Models\Guild;
use App\Models\Event;
use App\Core\Conversation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class stepController extends Controller
{
    public function check(Request $request)
    {
        $guild = Guild::where('discord_id', $request->guild_discord_id)->first();
        $event = Event::findFromChannelId($request->channel_discord_id);
        if (empty($event)) {
            Conversation::sendToDiscord($request->channel_discord_id, $guild, 'bot', 'cmd_no_current_event');
            return response()->json('', 400);
        }
        if ($event->type != 'train') {
            Conversation::sendToDiscord($request->channel_discord_id, $guild, 'bot', 'cmd_event_not_train');
            return response()->json('', 400);
        }

        $step = $event->train->getCurrentStep();
        if (!empty($step)) $step->check();

        return response()->json(null, 204);
    }

    public function uncheck(Request $request)
    {
        $guild = Guild::where('discord_id', $request->guild_discord_id)->first();
        $event = Event::findFromChannelId($request->channel_discord_id);
        if (empty($event)) {
            Conversation::sendToDiscord($request->channel_discord_id, $guild, 'bot', 'cmd_no_current_event');
            return response()->json('', 400);
        }
        if ($event->type != 'train') {
            Conversation::sendToDiscord($request->channel_discord_id, $guild, 'bot', 'cmd_event_not_train');
            return response()->json('', 400);
        }

        $step = $event->train->getCurrentStep();
        if (!empty($step)) {
            $previous = $step->getPreviousStep();
            if (!empty($previous)) $previous->uncheck();
        }

        return response()->json(null, 204);
    }
}