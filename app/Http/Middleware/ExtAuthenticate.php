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

        $token = $request->bearerToken();
        if (empty($token)) {
            $token = $request->token;
            if (empty($token)) return response()->json('You must specify a token', 403);
        }

        $guild_api_access = GuildApiAccess::where('key', $token)->first();
        if (!$guild_api_access || empty($guild_api_access)) return response()->json('Credentials are not valid', 403);

        $guild = Guild::find($guild_api_access->guild_id);
        if (empty($guild)) {
            GuildApiLog::create([
                'api_access_id' => $guild_api_access->id,
                'endpoint' => $request->path(),
                'status' => 404,
            ]);
            return response()->json('Guild does not exist', 404);
        }

        return $next($request);
    }
}