<?php

namespace App\Http\Controllers\App\Quests;

use App\Models\Pokemon;
use App\Models\QuestReward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class QuestRewardController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(QuestReward::all(), 200);
    }

    public function show(Request $request, QuestReward $quest_reward)
    {
        return response()->json($quest_reward, 200);
    }

    public function update(Request $request,  QuestReward $quest_reward)
    {
        $args = $request->all(); 
        $quest_reward->update($args);
        return response()->json($quest_reward, 200);
    }

    public function mega(Request $request)
    {
        $megas = Pokemon::whereIn('form_id', ['51', '52'])->orderBy('name_ocr')->get();
        $pokemons_ids = [];
        $pokemons = [];
        foreach($megas as $mega) {
            $pokemon = Pokemon::where('pokedex_id', $mega->pokedex_id)->where('form_id', '00')->first();
            if( !in_array($pokemon->id, $pokemons_ids) ) {
                $pokemons_ids[] = $pokemon->id;
                $pokemons[] = $pokemon;                
            }
        }
        return response()->json($pokemons, 200);
    }
}
