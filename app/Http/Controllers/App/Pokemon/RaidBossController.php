<?php

namespace App\Http\Controllers\App\Pokemon;

use App\Models\Pokemon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class RaidBossController extends Controller
{

    public function update(Request $request)
    {

        $levels = [
            ['level' => 1, 'value' => $request->bosses1t],
            ['level' => 2, 'value' => $request->bosses2t],
            ['level' => 3, 'value' => $request->bosses3t],
            ['level' => 4, 'value' => $request->bosses4t],
            ['level' => 5, 'value' => $request->bosses5t],
            ['level' => 6, 'value' => $request->bosses6t],
            ['level' => 7, 'value' => $request->bosses7t]
        ];

        $pokemon_ids = [];
        foreach ($levels as $level) {
            if (!empty($level['value'])) {
                foreach ($level['value'] as $boss) {
                    $pokemon = Pokemon::find($boss['id']);
                    if ($pokemon) {
                        $pokemon->update([
                            'boss' => 1,
                            'boss_level' => $level['level']
                        ]);
                        $pokemon_ids[] = $boss['id'];
                    }
                }
            }
        }

        $old_bosses = Pokemon::whereNotIn('id', $pokemon_ids)->get();
        if (!empty($old_bosses)) {
            foreach ($old_bosses as $old_boss) {
                $old_boss->update([
                    'boss' => 0,
                    'boss_level' => null
                ]);
            }
        }

        //Return
        $pokemons = Pokemon::where('boss', 1)
            ->get();
        return response()->json($pokemons, 200);
    }
}