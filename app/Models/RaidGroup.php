<?php

namespace App\Models;

use App\User;
use App\Models\Raid;
use App\Core\Helpers;
use App\Models\Guild;
use App\Models\Connector;
use RestCord\DiscordClient;
use App\Core\Discord\Discord;
use App\Models\DiscordMessage;
use App\Models\RaidParticipant;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use App\Core\Discord\Messages\RaidEmbed;
use App\Core\Discord\Messages\RaidChannelTopic;

class RaidGroup extends Model
{
    protected $fillable = ['guild_id', 'raid_id'];

    public static function boot()
    {
        parent::boot();
        static::created(function (RaidGroup $group) {
            $connector = Connector::find(request()->connector_id);
            if ($connector->add_channel) $group->addChannel($connector->id);
        });
    }

    public function addChannel($connector_id)
    {
        $connector = Connector::find($connector_id);

        $channel = Discord::createChannel([
            'guild.id' => (int) $this->guild->discord_id,
            'name' => $this->raid->egg_level . 't-' . Helpers::sanitize($this->raid->getGym()->name),
            'topic' => RaidChannelTopic::forRaid($this->raid)->forGuild($this->guild)->get(),
            'type' => 0,
            'parent_id' => (int) $connector->channel_category_discord_id,
            'permission_overwrites' => [
                [
                    'id' => Role::where('guild_id', $this->guild_id)->where('name', '@everyone')->first()->discord_id,
                    'type' => 0,
                    'deny' => '379968',
                    'deny_new' => '379968',
                ]
            ]
        ]);

        $end_time = \DateTime::createFromFormat('Y-m-d H:i:s', $this->raid->end_time);
        $result = DiscordChannel::create([
            'relation_type' => 'raid',
            'relation_id' => $this->raid->id,
            'guild_id' => $this->guild->id,
            'discord_id' => $channel->id,
            'connector_id' => $connector->id,
            'to_delete_at' => DiscordChannel::getChannelDeletionTime($end_time, $connector->channel_duration),
        ]);
        if( $result ) $this->initChannelMessage($connector);
    }

    public function getRaidAttribute()
    {
        return Raid::find($this->raid_id);
    }

    public function getGuildAttribute()
    {
        return Guild::find($this->guild_id);
    }

    public function participants()
    {
        return $this->hasMany('App\Models\RaidParticipant');
    }

    public function getChannelAttribute() {
        $channel = DiscordChannel::where('relation_type', 'raid')
            ->where('relation_id', $this->raid_id)
            ->where('guild_id', $this->guild_id)
            ->first();
        if( empty($channel) ) return false;
        return $channel;
    }

    public function getPinnedMessageAttribute() {
        $channel = DiscordMessage::where('relation_type', 'raid')
            ->where('relation_id', $this->raid_id)
            ->where('guild_id', $this->guild_id)
            ->where('type', 'channel-pinned')
            ->first();
        if( empty($channel) ) return false;
        return $channel;
    }

    private function initChannelMessage($connector)
    {
        $embed = RaidEmbed::forRaid($this->raid)
            ->forGuild($this->guild)
            ->setSettings(['cp', 'participants_list'])
            ->get();

        try {
            $discord = new DiscordClient(['token' => config('discord.token')]);
            $message = $discord->channel->createMessage(array(
                'channel.id' => intval($this->channel->discord_id),
                'content' => '',
                'embed' => $embed,
            ));

            DiscordMessage::create([
                'relation_type' => 'raid',
                'relation_id' => $this->raid_id,
                'guild_id' => $this->guild_id,
                'type' => 'channel-pinned',
                'connector_id' => $connector->id,
                'discord_id' => $message['id'],
                'channel_discord_id' => $this->channel->discord_id,
                'to_delete_at' => null,
            ]);

            $discord = new DiscordClient(['token' => config('discord.token')]);
            if ($connector->add_participants) {
                $icons = ['👤', '🚁', '🎟️', '❌'];
                if( $this->guild->settings->raidorga_nb_players ) $icons = ['👤', '🚁', '🎟️', '1️⃣', '2️⃣', '3️⃣', '❌'];
                foreach ($icons as $emoji) {
                    usleep(200000);
                    $result = $discord->channel->createReaction([
                        'channel.id' => intval($this->channel->discord_id),
                        'message.id' => intval($message['id']),
                        'emoji' => $emoji,
                    ]);
                }
            }

            $discord->channel->addPinnedChannelMessage([
                'channel.id' => intval($this->channel->discord_id),
                'message.id' => intval($message['id']),
            ]);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function updateChannelMessage()
    {
        if( empty($this->pinned_message) ) return;

        $embed = RaidEmbed::forRaid($this->raid)
            ->forGuild($this->guild)
            ->setSettings(['cp', 'participants_list'])
            ->get();

        try {
            $discord = new DiscordClient(['token' => config('discord.token')]);
            $message = $discord->channel->editMessage(array(
                'message.id' => intval($this->pinned_message->discord_id),
                'channel.id' => intval($this->channel->discord_id),
                'content' => '',
                'embed' => $embed,
            ));
        } catch (\Exception $e) {
            return false;
        }
    }

    public function updateChannelParticipants()
    {
        if( !$this->channel ) return;

        $permissions = [
            [
                'id' => Role::where('guild_id', $this->guild_id)->where('name', '@everyone')->first()->discord_id,
                'type' => 0,
                'deny' => '379968',
                'deny_new' => '379968',
            ]
        ];
        foreach($this->participants as $participant) {
            $permissions[] = [
                'id' => $participant->user->discord_id,
                'type' => 1,
                'allow' => "379968",
                'allow_new' => "379968",
            ];
        }

        $channel =Discord::modifyChannel([
            'channel.id' => (int) $this->channel->discord_id,
            'topic' => RaidChannelTopic::forRaid($this->raid)->forGuild($this->guild)->get(),
            'permission_overwrites' => $permissions
        ]);
    }

    public function add(User $user, $type = null, $accounts = null)
    {
        $participant = RaidParticipant::where('raid_group_id', $this->id)
            ->where('user_id', $user->id)
            ->first();

        if( empty($participant) ) {
            $participant = RaidParticipant::create(['raid_group_id' => $this->id, 'user_id' => $user->id]);
            if($this->channel && $this->guild->settings->raidorga_send_messages_participants) {
                \App\Core\Conversation::sendToDiscord($this->channel->discord_id, $this->guild, 'raid', 'add_participant', null, null, $user);
            }
        }

        if (!empty($type)) $participant->update(['type' => $type]);
        if (!empty($accounts)) $participant->update(['accounts' => $accounts]);

        $this->updateChannelMessage();
        $this->updateChannelParticipants();
        $this->updateDiscordMessages();
        return $participant;
    }

    public function remove(User $user)
    {
        $participant = RaidParticipant::where('raid_group_id', $this->id)->where('user_id', $user->id)->first();

        if (!empty($participant)) {
            if($this->channel && $this->guild->settings->raidorga_send_messages_participants) {
                \App\Core\Conversation::sendToDiscord($this->channel->discord_id, $this->guild, 'raid', 'remove_participant', null, null, $user);
            }
            $participant->delete();
        }

        $this->updateChannelMessage();
        $this->updateChannelParticipants();
        $this->updateDiscordMessages();
        return null;
    }

    public function getParticipants($type = false)
    {
        $participants = $this->participants();
        if ($type) $participants->where('type', $type);
        return $participants->get();
    }

    public function getNbParticipants($type = false)
    {
        $query = $this->participants();
        if ($type) $query->where('type', $type);
        $participants = $query->get();
        $num = 0;
        if (!empty($participants)) {
            foreach ($participants as $participant) {
                $num += $participant->accounts;
            }
        }
        return $num;
    }

    public function getListeParticipants($type = false)
    {
        $participants = $this->participants;
        if (empty($participants)) return '';
        $count = count($participants);
        $num = 0;
        $return = "";
        foreach ($participants as $participant) {
            $num++;
            $accounts = ($participant->accounts > 1) ? " x{$participant->accounts}" : '';
            $line = "  - {$participant->user->getNickname($this->guild_id)} ({$participant->type_label}){$accounts}";
            $return .= str_replace('  ', ' ', $line);
            if ($num < $count) $return .= "\r\n";
        }
        return $return;
    }

    public function updateDiscordMessages()
    {
        $messages = $this->raid->messages()->where('guild_id', $this->guild_id)->get();
        if (!empty($messages)) {
            foreach ($messages as $message) {
                if( empty($message->connector_id) ) continue;
                $connector = Connector::find($message->connector_id);
                $connector->editMessage($this->raid, $message);
            }
        }
    }
}
