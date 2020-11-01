<?php

namespace App\Http\Controllers\Ext;

use App\Core\Helpers;
use App\Models\Raid;
use App\Models\Stop;
use App\Models\Guild;
use App\Models\Pokemon;
use App\Models\GuildApiLog;
use Illuminate\Http\Request;
use App\Models\GuildApiAccess;
use App\Http\Controllers\Controller;

class RaidController extends Controller
{

  public function store(Request $request)
  {
    $guild_api_access = GuildApiAccess::where('key', $request->bearerToken())->first();
    $guild = Guild::find($guild_api_access->guild_id);

    $args = [];
    $args['source_type'] = 'ext';
    $args['user_id'] = $guild_api_access->user_id;
    $args['guild_id'] = $guild->id;
    $args['city_id'] = $guild->city_id;

    /**
     * BOSS SEARCH
     */
    $pokemon_valid = false;
    if ($request->pokemon_id) {
      $form = $request->pokemon_form_id ? $request->pokemon_form_id : '00';
      $pokemon = Pokemon::where('pokedex_id', $request->pokemon_id)
        ->where('form_id', $form)
        ->first();
      if ($pokemon) {
        $pokemon_valid = true;
        $args['pokemon_id'] = $pokemon->id;
      }
    } elseif ($request->egg_level && is_int($request->egg_level)) {
      $pokemon_valid = true;
      $args['egg_level'] = $request->egg_level;
    }

    /**
     * STARTIME SEARCH
     */
    $start_time_valid = false;
    if ($request->start_time && Helpers::validateDate($request->start_time)) {
      $start_time_valid = true;
      $args['start_time'] = $request->start_time;
    }

    /**
     * GYM SEEARCH
     */
    $gym_valid = false;
    if ($request->gym_name) {
      $gym = Stop::where('gym', 1)
        ->where('niantic_name', $request->gym_name)
        ->where('city_id', $guild->city_id)
        ->first();
      if ($gym) {
        $gym_valid = true;
        $args['gym_id'] = $gym->id;
      }
    }


    if (!$gym_valid || !$start_time_valid || !$pokemon_valid) {
      $missing = [];
      if (!$gym_valid) $missing[] = 'gym';
      if (!$start_time_valid) $missing[] = 'start time';
      if (!$pokemon_valid) $missing[] = 'boss or egg level';
      $messages = "missing parameters : " . implode('1, ', $missing);
      GuildApiLog::create([
        'api_access_id' => $guild_api_access->id,
        'endpoint' => $request->path(),
        'status' => 400,
      ]);
      return response()->json($messages, 400);
    }

    /**
     * CREATION DU RAID
     */
    $raid = Raid::add($args);
    if ($raid) {
      GuildApiLog::create([
        'api_access_id' => $guild_api_access->id,
        'endpoint' => $request->path(),
        'status' => 200,
      ]);
      return response()->json($raid, 200);
    } else {
      GuildApiLog::create([
        'api_access_id' => $guild_api_access->id,
        'endpoint' => $request->path(),
        'status' => 400,
      ]);
      return response()->json('Unknown error', 400);
    }
  }
}