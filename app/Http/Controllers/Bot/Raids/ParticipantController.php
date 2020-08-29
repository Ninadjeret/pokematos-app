<?php

namespace App\Http\Controllers\Bot\Raids;

use App\User;
use App\Models\Raid;
use App\Models\RaidGroup;
use App\Models\DiscordMessage;
use RestCord\DiscordClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class ParticipantController extends Controller
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

        $user = User::where('discord_id', $request->user_discord_id)->first();

        //On récupère le groupe, et on créé le canal si ce n'est pas déja fait
        $request->merge(['connector_id' => $message->connector_id]); // On récupère le connecteur pour savoir ou créer le canal de raid
        $raid_group = RaidGroup::firstOrCreate(['guild_id' => $message->guild_id, 'raid_id' => $raid->id]);

        //On ajout le participant ou on le met à jour
        $join_type = $request->join_type ? $request->join_type : null;
        $accounts = $request->accounts ? $request->accounts : null;
        $raid_group->add($user, $join_type, $accounts);

        //Si tout s'est bien passé, on supprime la réaction du joeur
        if ($request->join_type) $emoji = ($request->join_type == 'present') ? '👤' : '🚁';
        if ($request->accounts) {
            if ($request->accounts === 1) $emoji = '1️⃣';
            if ($request->accounts === 2) $emoji = '2️⃣';
            if ($request->accounts === 3) $emoji = '3️⃣';
        }
        $discord = new DiscordClient(['token' => config('discord.token')]);
        usleep(100000);
        $discord->channel->deleteUserReaction([
            'channel.id' => (int) $request->channel_discord_id,
            'message.id' => (int) $request->message_discord_id,
            'user.id' => (int) $request->user_discord_id,
            'emoji' => $emoji
        ]);
    }

    public function destroy(Request $request)
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

        $user = User::where('discord_id', $request->user_discord_id)->first();

        //On récupère le groupe, et on créé le canal si ce n'est pas déja fait
        $raid_group = RaidGroup::firstOrCreate(['guild_id' => $message->guild_id, 'raid_id' => $raid->id]);
        $raid_group->remove($user);

        //Si tout s'est bien passé, on supprime la réaction du joeur
        $discord = new DiscordClient(['token' => config('discord.token')]);
        usleep(100000);
        $discord->channel->deleteUserReaction([
            'channel.id' => (int) $request->channel_discord_id,
            'message.id' => (int) $request->message_discord_id,
            'user.id' => (int) $request->user_discord_id,
            'emoji' => '✖️'
        ]);
    }
}