<?php

namespace App\Models;

use App\User;
use App\Models\Raid;
use App\Core\Helpers;
use App\Core\Discord\Discord;
use App\Models\Guild;
use App\Models\Connector;
use App\Models\RaidParticipant;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;

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
            'type' => 0,
            'parent_id' => (int) $connector->channel_category_discord_id,
            /*'permission_overwrites' => [
                [
                    'id' => Role::where('guild_id', $group->guild_id)->where('name', '@everyone')->first()->discord_id,
                    'type' => 'role',
                    'allow' => 0,
                    'deny' => 805829713,
                    'allow_new' => 0,
                    'deny_new' => 805829713,
                ]
            ]*/
        ]);

        $end_time = \DateTime::createFromFormat('Y-m-d H:i:s', $this->raid->end_time);
        DiscordChannel::create([
            'relation_type' => 'raid',
            'relation_id' => $this->raid->id,
            'guild_id' => $this->guild->id,
            'discord_id' => $channel->id,
            'connector_id' => $connector->id,
            'to_delete_at' => DiscordChannel::getChannelDeletionTime($end_time, $connector->channel_duration),
        ]);
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

    public function add(User $user, $type = null, $accounts = null)
    {
        $participant = RaidParticipant::firstOrCreate(['raid_group_id' => $this->id, 'user_id' => $user->id]);
        if (!empty($type)) $participant->update(['type' => $type]);
        if (!empty($accounts)) $participant->update(['accounts' => $accounts]);
        $this->updateDiscordMessages();
        return $participant;
    }

    public function remove(User $user)
    {
        $participant = RaidParticipant::where('raid_group_id', $this->id)->where('user_id', $user->id)->first();;
        if (!empty($participant)) $participant->delete();
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
            $type = ($participant->type == 'remote') ? '(à distance)' : '';
            $accounts = ($participant->accounts > 1) ? "x{$participant->accounts}" : '';
            $line = "{$participant->user->getNickname($this->guild_id)} {$accounts} {$type}";
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
                $connector = Connector::find($message->connector_id);
                $connector->editMessage($this->raid, $message);
            }
        }
    }
}
