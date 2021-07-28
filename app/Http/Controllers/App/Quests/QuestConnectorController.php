<?php

namespace App\Http\Controllers\App\Quests;

use App\Core\Helpers;
use App\Models\Guild;
use Illuminate\Http\Request;
use App\Models\QuestConnector;
use App\Http\Controllers\Controller;

class QuestConnectorController extends Controller
{
    public function index(Request $request, Guild $guild)
    {
        $connecteurs = QuestConnector::where('guild_id', $guild->id)->get();
        return response()->json($connecteurs, 200);
    }

    public function show(Request $request, Guild $guild, QuestConnector $questconnector)
    {
        return response()->json($questconnector, 200);
    }

    public function store(Request $request, Guild $guild)
    {
        $args = $request->all();
        $args['guild_id'] = $guild->id;
        $args['filter_reward_reward'] = Helpers::extractIds($request->filter_reward_reward);
        $args['filter_reward_pokemon'] = Helpers::extractIds($request->filter_reward_pokemon);
        $args['filter_stop_zone'] = Helpers::extractIds($request->filter_stop_zone);
        $args['filter_stop_stop'] = Helpers::extractIds($request->filter_stop_stop);
        $connector = QuestConnector::create($args);
        return response()->json($connector, 200);
    }

    public function update(Request $request, Guild $guild, QuestConnector $questconnector)
    {
        $args = $request->all();
        $args['guild_id'] = $guild->id;
        $args['filter_reward_reward'] = Helpers::extractIds($request->filter_reward_reward);
        $args['filter_reward_pokemon'] = Helpers::extractIds($request->filter_reward_pokemon);
        $args['filter_stop_zone'] = Helpers::extractIds($request->filter_stop_zone);
        $args['filter_stop_stop'] = Helpers::extractIds($request->filter_stop_stop);
        $questconnector->update($args);
        return response()->json($questconnector, 200);
    }

    public function destroy(Request $request, Guild $guild, QuestConnector $questconnector)
    {
        QuestConnector::destroy($questconnector->id);
        return response()->json(null, 204);
    }

}
