<?php

use Illuminate\Support\Facades\Route;

/**
 * CITY ROUTES
 */
Route::group(['middleware' => ['can:city_access']], function () {
  Route::get('cities/{city}/gyms', 'App\PoisController@index');
  Route::post('cities/{city}/raids', 'App\Raids\RaidController@store');
  Route::put('cities/{city}/raids/{raid}', 'App\Raids\RaidController@store');
  Route::delete('cities/{city}/raids/{raid}', 'App\Raids\RaidController@destroy');
  Route::get('cities/{city}/last-changes', 'CityController@getLastChanges');

  Route::get('cities/{city}/ranking', 'App\Rankings\RankingController@show');
  Route::get('cities/{city}/ranking/short', 'App\Rankings\ShortRankingController@show');
});

/**
 * GUILD ROUTES
 */
Route::group(['middleware' => ['can:guild_manage']], function () {
  Route::resource('guilds/{guild}/connectors', 'App\Raids\ConnectorController');
  Route::resource('guilds/{guild}/questconnectors', 'App\Quests\QuestConnectorController');
  Route::resource('guilds/{guild}/api_access', 'App\Guilds\ApiAccessController');
  Route::put('guilds/{guild}/api_access/{api_access}/token', 'App\Guilds\ApiAccessController@updateToken');
});

/**
 * POKEMON ROUTES
 */
Route::get('pokemon', 'App\Pokemon\PokemonController@index');
Route::group(['middleware' => ['can:boss_edit']], function () {
  Route::put('pokemon/raidbosses', 'App\Pokemon\RaidBossController@update');
});
Route::group(['middleware' => ['can:pokemon_manage']], function () {
  Route::get('pokemon/gamemaster', 'App\Pokemon\GameMasterController@get');
  Route::put('pokemon/gamemaster', 'App\Pokemon\GameMasterController@update');
  Route::get('pokemon/{pokemon}', 'App\Pokemon\PokemonController@show');
  Route::put('pokemon/{pokemon}', 'App\Pokemon\PokemonController@update');
  Route::delete('pokemon/{pokemon}', 'App\Pokemon\PokemonController@destroy');
  Route::put('pokemon/{pokemon}/thumbnails', 'App\Pokemon\PokemonController@updateThumbnails');
});

/**
 * QUESTS ROUTES
 */
Route::get('quests/rewards', 'App\Quests\QuestRewardController@index');
Route::get('quests', 'App\Quests\QuestController@index');
Route::group(['middleware' => ['can:quest_edit']], function () {
    Route::resource('quests', 'App\Quests\QuestController', ['except' => ['index']]);
    Route::get('quests/rewards/mega', 'App\Quests\QuestRewardController@mega');
    Route::get('quests/rewards/types', 'App\Quests\QuestRewardTypeController@index');
    Route::get('quests/rewards/{quest_reward}', 'App\Quests\QuestRewardController@show');
    Route::put('quests/rewards/{quest_reward}', 'App\Quests\QuestRewardController@update');
});

/**
 * SETTINGS ROUTES
 */
Route::group(['middleware' => ['can:settings_manage']], function () {
  Route::get('settings', 'SettingController@get');
  Route::put('settings', 'SettingController@update');
});
