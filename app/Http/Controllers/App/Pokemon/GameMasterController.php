<?php

namespace App\Http\Controllers\App\Pokemon;

use App\Models\Pokemon;
use Illuminate\Http\Request;
use App\Core\Tools\GameMaster;
use App\Http\Controllers\Controller;

class GameMasterController extends Controller
{
    public function get(Request $request)
    {
        $result = GameMaster::toUpdate();
        $to_add = count($result->to_add);
        $needs_update = false;
        $message = "<p>{$to_add} Pokémon à ajouter</p>";
        if ($to_add > 0) {
            $needs_update = true;
        }
        return response()->json(['message' => $message, 'needs_update' => $needs_update], 200);
    }

    public function update(Request $request)
    {
        $result = GameMaster::toUpdate();
        if (!empty($result->to_add)) {
            foreach ($result->to_add as $niantic_id => $data) {
                Pokemon::create([
                    'pokedex_id'    => $data['pokedex_id'],
                    'form_id'       => $data['form_id'],
                    'niantic_id'    => $niantic_id,
                    'name_fr'       => $data['name_fr'],
                    'name_ocr'      => $data['name_ocr'],
                    'base_att'      => $data['base_att'],
                    'base_def'      => $data['base_def'],
                    'base_sta'      => $data['base_sta'],
                    'parent_id'     => $data['parent_id'],
                ]);
            }
        }
        return response()->json(['message' => '<p>Mise à jour effectuée avec succès</p>', 'needs_update' => false], 200);
    }
}