<?php

namespace App\Core\Raids;

use App\Models\Pokemon;
use Illuminate\Support\Facades\Log;

class RaidHelpers
{
  public static function getPokemonForCp($raid)
  {
    if ($raid->egg_level != 7) return $raid->pokemon;
    return Pokemon::where('pokedex_id', $raid->pokemon->pokedex_id)
      ->where('form_id', '00')
      ->first();
  }
}