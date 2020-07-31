<?php

namespace App\Http\Middleware;

use Closure;
use App\Core\Conversation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @return mixed
   */
  public function handle($request, Closure $next, $permission)
  {

    //We check if permission exists. must be registered in permissions array
    $permissions = \App\User::getPermissions();
    if (!array_key_exists($permission, $permissions)) {
      return response()->json('La permission n\'existe pas', 403);
    }

    //regarding context, we add data to perform peform permission check
    $context = $permissions[$permission]['context'];
    switch ($context) {
      case 'global':
        $context_data = [];
        break;
      case 'specific':
        $context_data = [];
        break;
      case 'city':
        $city_id = (isset($request->city)) ? $request->city->id : false;
        $context_data = ['city_id' => $city_id];
        break;
      case 'guild':
        $guild_id = (isset($request->guild)) ? $request->guild->id : \App\Models\Guild::where('discord_id', $request->guild_discord_id)->first()->id;
        $context_data = ['guild_id' => $guild_id];
        break;
    }

    //get user & Permission check
    if (isset($request->user_discord_id) && isset($request->user_discord_roles)) {

      $user = \App\User::firstOrCreate(
        ['discord_id' => $request->user_discord_id],
        ['name' => $request->user_discord_name, 'password' => Hash::make(str_random(20))]
      );

      $guild = \App\Models\Guild::where('discord_id', $request->guild_discord_id)->first();
      if (empty($guild)) return response()->json('La guild n\'existe pas', 403);

      $user->CheckGuildPermissions($guild, $request->user_discord_roles, $request->user_discord_permissions);
      if (!$user->can($permission, $context_data)) {
        Conversation::sendToDiscord($request->channel_discord_id, $guild, 'bot', 'cmd_no_permission');
        return response()->json('Vous n\'avez pas les droits suffisants pour cette action', 403);
      }
    } else {
      $user = $request->user();
      if (!$user->can($permission, $context_data)) {
        return response()->json('Vous n\'avez pas les droits suffisants pour cette action', 403);
      }
    }

    //If user has permissions
    return $next($request);
  }
}