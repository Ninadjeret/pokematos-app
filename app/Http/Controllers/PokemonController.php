<?php

namespace App\Http\Controllers;

use App\Models\Quest;
use App\Models\Pokemon;
use App\Models\QuestReward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PokemonController extends Controller
{

    public function getQuestRewards(Request $request)
    {
        return response()->json(QuestReward::all(), 200);
    }

    public function getQuests(Request $request)
    {
        $quests = Quest::orderBy('name', 'asc')->get();
        return response()->json($quests, 200);
    }

    public function createQuest(Request $request)
    {
        $user = Auth::user();
        if (!$user->can('quest_edit')) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }

        $connector = Quest::create([
            'name' => $request->name,
            'pokemon_ids' => ($request->pokemon_ids) ? $request->pokemon_ids : null,
            'reward_ids' => ($request->reward_ids) ? $request->reward_ids : null,
        ]);
        return response()->json($connector, 200);
    }

    public function getQuest(Request $request, Quest $quest)
    {
        $user = Auth::user();
        if (!$user->can('quest_edit')) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
        return response()->json($quest, 200);
    }

    public function updateQuest(Request $request, Quest $quest)
    {
        $user = Auth::user();
        if (!$user->can('quest_edit')) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
        $quest->update([
            'name' => ($request->name) ? $request->name : $quest->name,
            'pokemon_ids' => ($request->pokemon_ids) ? $request->pokemon_ids : null,
            'reward_ids' => ($request->reward_ids) ? $request->reward_ids : null,
        ]);
        return response()->json($quest, 200);
    }

    public function deleteQuest(Request $request, Quest $quest)
    {
        $user = Auth::user();
        if (!$user->can('quest_edit')) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
        Quest::destroy($quest->id);
        return response()->json(null, 204);
    }

    public function getPokedexIdFromNameFr(Request $request, $name)
    {
        $pokemon = Pokemon::where('name_fr', $name)->first();
        if ($pokemon) {
            $id = $pokemon->pokedex_id;
            $id = ltrim($id, '0');
            return $id;
        }
        return response()->json('Pokémon non trouvé', 404);
    }
}