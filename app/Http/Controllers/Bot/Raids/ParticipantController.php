<?php

namespace App\Http\Controllers\Bot\Raids;

use App\User;
use App\Models\Raid;
use App\Models\RaidGroup;
use RestCord\DiscordClient;
use Illuminate\Http\Request;
use App\Models\DiscordChannel;
use App\Models\DiscordMessage;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class ParticipantController extends Controller
{
    public function store(Request $request)
    {

        $user = User::initFromBotRequest($request);
        $message = false;
        $channel = false;

        //From message = reaction
        if( !empty($request->message_discord_id) ) {
            $message = DiscordMessage::where('discord_id', $request->message_discord_id)
                ->where('relation_type', 'raid')
                ->first();
            if (empty($message)) return response()->json('cmd_no_raid_message', 400);

            $raid = Raid::find($message->relation_id);
            if (empty($raid)) return response()->json('cmd_no_raid', 400);

            $request->merge(['connector_id' => $message->connector_id]); // On récupère le connecteur pour savoir ou créer le canal de raid
            $raid_group = RaidGroup::firstOrCreate(['guild_id' => $message->guild_id, 'raid_id' => $raid->id]);
        }

        //From channel = command
        elseif( !empty($request->channel_discord_id) ) {
            $channel = DiscordChannel::where('discord_id', $request->channel_discord_id)
                ->where('relation_type', 'raid')
                ->first();
            if (empty($channel)) return response()->json('cmd_no_raid_message', 400);

            $raid = Raid::find($channel->relation_id);
            if (empty($raid)) return response()->json('cmd_no_raid', 400);

            $raid_group = RaidGroup::where('guild_id', $channel->guild_id)->where('raid_id', $raid->id)->first();
        }

        //Else exit
        else {
            return response()->json('cmd_no_raid_message', 400);
        }

        //On ajout le participant ou on le met à jour
        $join_type = $request->join_type ? $request->join_type : null;
        $accounts = $request->accounts ? $request->accounts : null;
        $raid_group->add($user, $join_type, $accounts);

        //On s'arrète là si ça venait d'une commande plutot que d'une réaction
        if(!$message) return;

        //Si tout s'est bien passé, on supprime la réaction du joeur
        if ($request->join_type) {
            if($request->join_type == 'present') $emoji = '👤';
            if($request->join_type == 'remote') $emoji = '🚁';
            if($request->join_type == 'invit') $emoji = '🎟️';
        }
        if ($request->accounts) {
            if ($request->accounts === 1) $emoji = '1️⃣';
            if ($request->accounts === 2) $emoji = '2️⃣';
            if ($request->accounts === 3) $emoji = '3️⃣';
        }
        $discord = new DiscordClient(['token' => config('discord.token')]);
        usleep(100000);
        $result = $discord->channel->deleteUserReaction([
            'channel.id' => (int) $request->channel_discord_id,
            'message.id' => (int) $request->message_discord_id,
            'user.id' => (int) $request->user_discord_id,
            'emoji' => $emoji
        ]);
    }

    public function destroy(Request $request)
    {
        $user = User::initFromBotRequest($request);

        //From message = reaction
        if( !empty($request->message_discord_id) ) {
            $message = DiscordMessage::where('discord_id', $request->message_discord_id)
                ->where('relation_type', 'raid')
                ->first();
            if (empty($message)) return response()->json('cmd_no_raid_message', 400);

            $raid = Raid::find($message->relation_id);
            if (empty($raid)) return response()->json('cmd_no_raid', 400);
        }

        //From channel = command
        elseif( !empty($request->channel_discord_id) ) {
            $channel = DiscordChannel::where('discord_id', $request->channel_discord_id)
                ->where('relation_type', 'raid')
                ->first();
            if (empty($channel)) return response()->json('cmd_no_raid_message', 400);

            $raid = Raid::find($channel->relation_id);
            if (empty($raid)) return response()->json('cmd_no_raid', 400);
        }

        //Else exit
        else {
            return response()->json('cmd_no_raid_message', 400);
        }

        $raid_group = RaidGroup::firstOrCreate(['guild_id' => $message->guild_id, 'raid_id' => $raid->id]);
        $raid_group->remove($user);

        //On s'arrète là si ça venait d'une commande plutot que d'une réaction
        if(!$message) return;

        //Si tout s'est bien passé, on supprime la réaction du joeur
        $discord = new DiscordClient(['token' => config('discord.token')]);
        usleep(100000);
        $discord->channel->deleteUserReaction([
            'channel.id' => (int) $request->channel_discord_id,
            'message.id' => (int) $request->message_discord_id,
            'user.id' => (int) $request->user_discord_id,
            'emoji' => '❌'
        ]);
    }
}
