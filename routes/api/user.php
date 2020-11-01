<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['can:city_access']], function () {
  Route::get('cities/{city}/gyms', 'App\PoisController@index');
  Route::post('cities/{city}/raids', 'App\Raids\RaidController@store');
  Route::put('cities/{city}/raids/{raid}', 'App\Raids\RaidController@store');
  Route::delete('cities/{city}/raids/{raid}', 'App\Raids\RaidController@destroy');
  Route::get('cities/{city}/last-changes', 'CityController@getLastChanges');
});

Route::group(['middleware' => ['can:guild_manage']], function () {
  Route::resource('guilds/{guild}/connectors', 'App\Raids\ConnectorController');
  Route::resource('guilds/{guild}/api_access', 'App\Guilds\ApiAccessController');
  Route::put('guilds/{guild}/api_access/{api_access}/token', 'App\Guilds\ApiAccessController@updateToken');
});

/**
 * POKEMON ROUTES
 */
Route::get('pokemon', 'App\Pokemon\PokemonController@index');
Route::group(['middleware' => ['can:pokemon_manage']], function () {
  Route::put('pokemon/raidbosses', 'PokemonController@updateRaidBosses');
});
Route::group(['middleware' => ['can:boss_edit']], function () {
  Route::get('pokemon/gamemaster', 'App\Pokemon\GameMasterController@get');
  Route::put('pokemon/gamemaster', 'App\Pokemon\GameMasterController@update');
  Route::get('pokemon/{pokemon}', 'App\Pokemon\PokemonController@show');
  Route::put('pokemon/{pokemon}', 'App\Pokemon\PokemonController@update');
});