<?php

namespace App\Http\Controllers\App\Guilds;

use App\User;
use App\Models\Guild;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GuildController extends Controller
{

    public function index(Request $request)
    {
        $user = User::find(1);
        return response()->json($user->getGuilds(), 200);
    }

    public function show(Request $request, Guild $guild)
    {
        $guild = Guild::find($guild->id);
        return response()->json($guild, 200);
    }
}