<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\Guild;
use App\Helpers\Discord;
use Illuminate\Http\Request;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function getVersion( Request $request ) {
        return response()->json(config('app.version'), 200);
    }

    public function getFeatures( Request $request ) {
        return response()->json(config('features'), 200);
    }

    public function test( Request $request ) {
        $guild = Guild::find(1);
        $user = User::find(1);
        $message = "Coucou <@!287549031087996928> <@!630740660537786371>";
        $decoded = Discord::translateFrom($message, $guild, $user);
        return response()->json($decoded, 200);
    }
}
