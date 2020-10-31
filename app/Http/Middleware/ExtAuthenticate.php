<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Guild;
use App\Models\GuildApiAccess;
use App\Models\GuildApiLog;
use Illuminate\Support\Facades\Log;

class ExtAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $guild = Guild::find($request->guild);
        if (!$guild || empty($guild)) return response()->json('Guild does not exists', 404);

        $token = $request->bearerToken();
        if (empty($token)) return response()->json('You must specify a bearer token', 403);

        $guild_api_access = GuildApiAccess::where('key', $token)->first();
        if (!$guild_api_access || empty($guild_api_access)) return response()->json('Credentials are not valid', 403);

        if ($guild_api_access->guild_id != $guild->id) {
            GuildApiLog::create([
                'api_access_id' => $guild_api_access->id,
                'endpoint' => $request->path(),
                'status' => 403,
            ]);
            return response()->json('You can not access to this guild with those credentials', 403);
        }

        return $next($request);
    }
}