<?php

namespace App\Http\Controllers\Bot\Raids;

use App\User;
use App\Models\Raid;
use App\Models\RaidGroup;
use RestCord\DiscordClient;
use Illuminate\Http\Request;
use App\Models\DiscordMessage;
use App\Http\Controllers\Controller;
use App\Models\Connector;

class ChannelController extends Controller
{
    public function store(Request $request)
    {
        $user = User::initFromBotRequest($request);
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

        $connector = Connector::find($message->connector_id);

        $request->merge(['connector_id' => $connector->id]); // On récupère le connecteur pour savoir ou créer le canal de raid
        $raid_group = RaidGroup::firstOrCreate(['guild_id' => $message->guild_id, 'raid_id' => $raid->id]);

        if( $connector->add_participants ) {
            $raid_group->add($user, 'present', 1);
        }

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
