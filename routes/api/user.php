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