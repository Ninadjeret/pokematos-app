<?php

namespace App\Http\Controllers\App\Guilds;

use App\Models\Guild;
use Illuminate\Http\Request;
use App\Models\GuildApiAccess;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class ApiAccessController extends Controller
{

    public function index(Request $request, Guild $guild)
    {
        $api_access = GuildApiAccess::where('guild_id', $guild->id)->get();
        return response()->json($api_access, 200);
    }

    public function store(Request $request, Guild $guild)
    {
        $args = $request->all();
        $args['guild_id'] = $guild->id;
        if (!is_array($args['authorizations'])) $args['authorizations'] = [$args['authorizations']];
        $api_access = GuildApiAccess::create($args);
        $api_access->editUser($args);
        return response()->json($api_access, 200);
    }

    public function update(Request $request, Guild $guild, GuildApiAccess $api_access)
    {
        $args = $request->all();
        $args['guild_id'] = $guild->id;
        if (!is_array($args['authorizations'])) $args['authorizations'] = [$args['authorizations']];
        $api_access->update($args);
        $api_access->editUser($args);
        return response()->json($api_access, 200);
    }

    public function show(Request $request, Guild $guild, GuildApiAccess $api_access)
    {
        return response()->json($api_access, 200);
    }

    public function destroy(Request $request, Guild $guild, GuildApiAccess $api_access)
    {
        GuildApiAccess::destroy($api_access->id);
        return response()->json(null, 204);
    }

    public function updateToken(Request $request, Guild $guild, GuildApiAccess $api_access)
    {
        $api_access->generateToken();
        return response()->json($api_access, 200);
    }
}