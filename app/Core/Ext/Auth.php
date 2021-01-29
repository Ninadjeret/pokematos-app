<?php

namespace App\Core\Ext;

use App\Models\Role;
use RestCord\DiscordClient;
use App\Models\GuildApiAccess;
use Illuminate\Support\Facades\Log;

class Auth
{
  public static function access()
  {
    $token = request()->bearerToken();
    if (empty($token)) {
      $token = request()->token;
      if (empty($token)) return false;
    }
    $guild_api_access = GuildApiAccess::where('key', $token)->first();
    if (empty($guild_api_access)) return false;
    return $guild_api_access;
  }
}