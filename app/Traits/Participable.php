<?php

namespace App\Traits;

use App\Models\Guild;
use App\Models\RaidGroup;
use App\Models\Connector;
use Illuminate\Support\Facades\Log;

trait Participable
{

  public function channels()
  {
    return $this->morphMany('App\Models\DiscordChannel', 'relation');
  }

  public function messages()
  {
    return $this->morphMany('App\Models\DiscordMessage', 'relation');
  }

  public function groups()
  {
    return $this->hasMany('App\Models\RaidGroup');
  }

  public function getGuildGroup($guild_id)
  {
    $group = RaidGroup::where('raid_id', $this->id)->where('guild_id', $guild_id)->first();
    if (empty($group)) return false;
    return $group;
  }

  public function getParticipableGuildsAttribute()
  {
    $messages = $this->messages;
    if (empty($messages)) return [];
    $return = [];
    foreach ($messages as $message) {
      if (array_key_exists($message->guild_id, $return)) continue;

      $connector = Connector::find($message->connector_id);
      $channel = $this->channels()->where('guild_id', $message->guild_id)->first();
      $group = $this->groups()->where('guild_id', $message->guild_id)->first();

      $return[$message->guild_id] = [
        'channelable' => $connector->add_channel,
        'participable' => $connector->add_participants,
        'raid_group' => $group ? $group->toArray() : false,
        'channel' => $channel ? $channel->toArray() : false,
      ];
    }
    return $return;
  }
}