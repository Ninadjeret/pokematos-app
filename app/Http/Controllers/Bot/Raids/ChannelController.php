<?php

namespace App\Http\Controllers\Bot\Raids;

use App\Models\Raid;
use App\Models\RaidGroup;
use RestCord\DiscordClient;
use Illuminate\Http\Request;
use App\Models\DiscordMessage;
use App\Http\Controllers\Controller;

class ChannelController extends Controller
{
    public function store(Request $request)
    {
        $message = DiscordMessage::where('discord_id', $request->message_discord_id)
            ->where('relation_type', 'raid')
            ->first();
        if (empty($message)) {
            return response()->json('cmd_no_raid_message', 400);
        }
        $raid = Raid::find($message->relation_id);
        if (empty($raid)) {
            return response()->json('cmd_no_raid', 400);
        }

        $request->merge(['connector_id' => $message->connector_id]); // On récupère le connecteur pour savoir ou créer le canal de raid
        RaidGroup::firstOrCreate(['guild_id' => $message->guild_id, 'raid_id' => $raid->id]);

        $discord = new DiscordClient(['token' => config('discord.token')]);
        usleep(100000);
        $discord->channel->deleteUserReaction([
            'channel.id' => (int) $request->channel_discord_id,
            'message.id' => (int) $request->message_discord_id,
            'user.id' => (int) $request->user_discord_id,
            'emoji' => '#️⃣'
        ]);
    }
}