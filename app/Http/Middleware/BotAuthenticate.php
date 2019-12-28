<?php

namespace App\Http\Middleware;

use Closure;

class BotAuthenticate
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
        if( empty($token) || $token != config('app.bot_token') ) {
            return response()->json('Wrong token', 400);
        }
        return $next($request);
    }
}
