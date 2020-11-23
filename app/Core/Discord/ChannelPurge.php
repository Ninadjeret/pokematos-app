<?php

namespace App\Core\Discord;

use App\Models\DiscordChannel;

class ChannelPurge
{

  public static function purge()
  {
    DiscordChannel::where('to_delete_at', '<', date('Y-m-d H:i:s'))->get()->each(function ($channel) {
      $channel->suppr();
    });
  }
}