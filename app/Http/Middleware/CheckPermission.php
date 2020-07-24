<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
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
    Log::debug(print_r($request->city, true));

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
        $context_data = ['city_id' => $request->city->id];
        break;
      case 'guild':
        $context_data = ['guild_id' => $request->guild->id];
        break;
    }

    //Permission check
    $user = $request->user();
    if (!$user->can($permission, $context_data)) {
      return response()->json('Vous n\'avez pas les droits suffisants pour cette action', 403);
    }

    //If user has permissions
    return $next($request);
  }
}