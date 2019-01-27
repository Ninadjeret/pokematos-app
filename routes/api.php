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

    /*** Cities ***/


});
Route::get('cities', 'CityController@getAll');
Route::get('cities/{city}', 'CityController@getOne');
Route::get('guilds', 'GuildController@getAll');
Route::get('guilds/{guild}', 'GuildController@getOne');
Route::get('gyms', 'GymController@getAll');
