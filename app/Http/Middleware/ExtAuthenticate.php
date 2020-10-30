<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Guild;
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
        if (!$guild || empty($guild)) return response()->json('Guild does not exists', 400);

        $token = $request->bearerToken();
        if (empty($token) || $token != config('app.bot_token')) {
            return response()->json('Wrong token', 400);
        }
        return $next($request);
    }
}