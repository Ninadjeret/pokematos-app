<?php

namespace App\Core\Tools;

use \Intervention\Image\Facades\Image;

class ThumbnailMaker
{

  public static function forPokemonRaid($pokemon)
  {
    $path = "https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/pokemon_icon_{$pokemon->pokedex_id}_{$pokemon->form_id}.png";
    $save = storage_path() . "/app/public/img/pokemon/raid/marker_{$pokemon->pokedex_id}_{$pokemon->form_id}.png";
    $img = Image::make(storage_path() . '/app/sources/map_marker_raid_empty.png');

    $item = Image::make($path);
    $item->resize(139, 92, function ($constraint) {
        $constraint->aspectRatio();
    });

    $img->insert($item, 'bottom', 0, 52);
    $img->save($save);
  }

  public static function forPokemonQuest($pokemon)
  {
    $path = "https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/pokemon_icon_{$pokemon->pokedex_id}_{$pokemon->form_id}.png";
    $save = storage_path() . "/app/public/img/pokemon/quest/marker_{$pokemon->pokedex_id}_{$pokemon->form_id}.png";
    $img = Image::make(storage_path() . '/app/sources/map_marker_quest_empty.png');

    $item = Image::make($path);
    $item->resize(139, 92, function ($constraint) {
        $constraint->aspectRatio();
    });

    $img->insert($item, 'bottom', 0, 52);
    $img->save($save);
  }

  public static function forPokemonEnergyBase($pokemon)
  {
    $path = "https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/pokemon_icon_{$pokemon->pokedex_id}_{$pokemon->form_id}.png";
    $save = storage_path() . "/app/public/img/pokemon/energy/megaenergy_{$pokemon->pokedex_id}.png";
    $img = Image::make(storage_path() . '/app/sources/mega_energy.png');

    $pkmn = Image::make($path);
    $pkmn->resize(null, 120, function ($constraint) {
        $constraint->aspectRatio();
    });

    $img->insert($pkmn, 'bottom-right', 0, 0);
    $img->save($save);
  }

  public static function forPokemonEnergyQuest($pokemon)
  {
    $path = "https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Pokemon/pokemon_icon_{$pokemon->pokedex_id}_{$pokemon->form_id}.png";
    $save = storage_path() . "/app/public/img/pokemon/energyquest/megaenergy_{$pokemon->pokedex_id}.png";
    $img = Image::make(storage_path() . '/app/sources/map_marker_quest_energy.png');

    $pkmn = Image::make($path);
    $pkmn->resize(null, 60, function ($constraint) {
        $constraint->aspectRatio();
    });
    
    $img->insert($pkmn, 'bottom-right', 0, 52);
    $img->save($save);
  }

  public static function forItemBase($type) {
    $path = "https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Items/{$type->niantic_id}.png";
    $save = storage_path() . "/app/public/img/items/base/item_{$type->slug}.png";
    $img = Image::make(storage_path() . '/app/sources/empty.png');

    $item = Image::make($path);
    $item->resize(240, 240, function ($constraint) {
        $constraint->aspectRatio();
    });
    
    $img->insert($item, 'center');
    $img->save($save);    
  } 

  public static function forItemQuest($type) {
    $path = "https://raw.githubusercontent.com/PokeMiners/pogo_assets/master/Images/Items/{$type->niantic_id}.png";
    $save = storage_path() . "/app/public/img/items/quest/item_{$type->slug}.png";

    $img = Image::make(storage_path() . '/app/sources/map_marker_quest_empty.png');

    $item = Image::make($path);
    $item->resize(120, 90, function ($constraint) {
        $constraint->aspectRatio();
    });
    
    $img->insert($item, 'bottom', 0, 52);
    $img->save($save);    
  } 
}