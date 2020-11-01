<?php

namespace App\Http\Middleware;

use Closure;
use App\Core\Conversation;
use App\Models\GuildApiLog;
use App\Models\GuildApiAccess;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CheckExtPermission
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

    $token = $request->bearerToken();
    $guild_api_access = GuildApiAccess::where('key', $token)->first();

    if (empty($guild_api_access->authorizations) || !in_array($permission, $guild_api_access->authorizations)) {
      GuildApiLog::create([
        'api_access_id' => $guild_api_access->id,
        'endpoint' => $request->path(),
        'status' => 403,
      ]);
      return response()->json('You can not access to this endpoint', 403);
    }

    //If user has permissions
    return $next($request);
  }
}