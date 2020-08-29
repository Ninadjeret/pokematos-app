<?php

namespace App\Http\Controllers\App\Raids;

use App\Core\Helpers;
use App\Models\Guild;
use App\Models\Connector;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ConnectorController extends Controller
{
    public function index(Request $request, Guild $guild)
    {
        $connecteurs = Connector::where('guild_id', $guild->id)->get();
        return response()->json($connecteurs, 200);
    }

    public function store(Request $request, Guild $guild)
    {
        $args = $request->all();
        $args['guild_id'] = $guild->id;
        $args['filter_gym_zone'] = Helpers::extractIds($request->filter_gym_zone);
        $args['filter_gym_gym'] = Helpers::extractIds($request->filter_gym_gym);
        $args['filter_pokemon_level'] = Helpers::extractIds($request->filter_pokemon_level);
        $args['filter_pokemon_pokemon'] = Helpers::extractIds($request->filter_pokemon_pokemon);
        $connector = Connector::create($args);
        return response()->json($connector, 200);
    }

    public function update(Request $request, Guild $guild, Connector $connector)
    {
        $args = $request->all();
        $args['guild_id'] = $guild->id;
        $args['filter_gym_zone'] = Helpers::extractIds($request->filter_gym_zone);
        $args['filter_gym_gym'] = Helpers::extractIds($request->filter_gym_gym);
        $args['filter_pokemon_level'] = Helpers::extractIds($request->filter_pokemon_level);
        $args['filter_pokemon_pokemon'] = Helpers::extractIds($request->filter_pokemon_pokemon);
        $connector->update($args);
        return response()->json($connector, 200);
    }

    public function show(Request $request, Guild $guild, Connector $connector)
    {
        return response()->json($connector, 200);
    }

    public function destroy(Request $request, Guild $guild, Connector $connector)
    {
        Connector::destroy($connector->id);
        return response()->json(null, 204);
    }
}