<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\models\Guild;
use App\User;

class GuildController extends Controller {

    public function getAll(Request $request){
        $user = User::find(1);
        return response()->json($user->getGuilds(), 200);
    }

    public function getOne(Request $request, Guild $guild){
        $guild = Guild::find($guild->id);
        return response()->json($guild, 200);
    }
}
