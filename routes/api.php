<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['auth:api']], function () {



});

/*** Cities ***/
Route::get('user/cities', 'CityController@getAll');
Route::get('user/cities/{city}', 'CityController@getOne');
Route::get('user/guilds', 'GuildController@getAll');
Route::get('user/guilds/{guild}', 'GuildController@getOne');
Route::get('user/cities/{city}/gyms', 'GymController@getCityGyms');
Route::get('user/cities/{city}/raids', 'RaidController@getCityRaids');

Route::get('pokemons', 'PokemonController@getAll');
Route::get('pokemons/raidbosses', 'PokemonController@getRaidBosses');
