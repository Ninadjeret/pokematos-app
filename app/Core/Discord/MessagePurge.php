<?php

namespace App\Core\Discord;

use App\Models\DiscordMessage;

class MessagePurge
{

  public static function purge()
  {
    DiscordMessage::where('to_delete_at', '<', date('Y-m-d H:i:s'))->get()->each(function ($message) {
      $message->suppr();
    });
  }
}