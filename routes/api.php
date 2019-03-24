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

    //user
    Route::get('user', 'UserController@getUSer');
    Route::get('user/cities', 'UserController@getCities');
    Route::get('user/cities/{city}', 'CityController@getOne');
    Route::get('user/guilds', 'GuildController@getAll');
    Route::get('user/guilds/{guild}', 'GuildController@getOne');
    Route::get('user/cities/{city}/gyms', 'GymController@getCityGyms');
    Route::get('user/cities/{city}/raids', 'RaidController@getCityRaids');
    Route::post('user/cities/{city}/raids', 'RaidController@create');
    Route::put('user/cities/{city}/raids/{raid}', 'RaidController@create');
    Route::delete('user/cities/{city}/raids/{raid}', 'RaidController@delete');

    //Admin
    Route::get('user/cities/{city}/zones', 'CityController@getZones');
    Route::post('user/cities/{city}/zones', 'CityController@createZone');
    Route::get('user/cities/{city}/zones/{zone}', 'CityController@getZone');
    Route::put('user/cities/{city}/zones/{zone}', 'CityController@saveZone');
    Route::delete('user/cities/{city}/zones/{zone}', 'CityController@deleteZone');

    Route::post('user/cities/{city}/gyms', 'CityController@createGym');
    Route::get('user/cities/{city}/gyms/{stop}', 'CityController@getGym');
    Route::put('user/cities/{city}/gyms/{stop}', 'CityController@saveGym');
    Route::delete('user/cities/{city}/gyms/{stop}', 'CityController@deleteGym');

    //ADMIN / Guilds
    Route::get('user/cities/{city}/guilds/{guild}/roles', 'DiscordController@getRoles');
<<<<<<< HEAD
    Route::get('user/cities/{city}/guilds/{guild}/channels', 'DiscordController@getChannels');
    Route::get('user/cities/{city}/guilds/{guild}/settings', 'UserController@getGuildOptions');
    Route::put('user/cities/{city}/guilds/{guild}/settings', 'UserController@updateGuildOptions');

    Route::get('user/guilds/{guild}/roles', 'UserController@getRoles');
    Route::post('user/guilds/{guild}/roles', 'UserController@createRole');
    Route::get('user/guilds/{guild}/roles/{role}', 'UserController@getRole');
    Route::put('user/guilds/{guild}/roles/{role}', 'UserController@updateRole');
    Route::delete('user/guilds/{guild}/roles/{role}', 'UserController@deleteRole');

    Route::get('user/guilds/{guild}/rolecategories', 'UserController@getRoleCategories');
    Route::post('user/guilds/{guild}/rolecategories', 'UserController@createRoleCategory');
    Route::get('user/guilds/{guild}/rolecategories/{categorie}', 'UserController@getRoleCategory');
    Route::put('user/guilds/{guild}/rolecategories/{categorie}', 'UserController@updateRoleCategory');
    Route::delete('user/guilds/{guild}/rolecategories/{categorie}', 'UserController@deleteRoleCategory');

=======
    Route::get('user/cities/{city}/guilds/{guild}/settings', 'UserController@getGuildOptions');
    Route::put('user/cities/{city}/guilds/{guild}/settings', 'UserController@updateGuildOptions');

>>>>>>> 7825fa41493a2ae4c4324d72e593c91c40f72664
    //commun
    Route::get('pokemons', 'PokemonController@getAll');
    Route::get('pokemons/raidbosses', 'PokemonController@getRaidBosses');
    Route::put('pokemons/raidbosses', 'PokemonController@updateRaidBosses');

});

Route::get('bot/roles', 'BotController@getRoles');
Route::post('bot/roles', 'BotController@createRole');
Route::delete('bot/roles/{role}', 'BotController@deleteRole');
Route::get('bot/roles/{role}', 'BotController@getRole');
Route::put('bot/roles/{role}', 'BotController@updateRole');
<<<<<<< HEAD

Route::get('bot/rolecategories', 'BotController@getRoleCategories');
Route::post('bot/rolecategories', 'BotController@createRoleCategory');
Route::get('bot/rolecategories/{categorie}', 'BotController@getRoleCategory');
Route::delete('bot/rolecategories/{categorie}', 'deleteRoleCategory@getRoleCategory');

Route::get('bot/rolecategories/{categorie}/permissions', 'BotController@getRoleCategoryPermissions');
Route::post('bot/rolecategories/{categorie}/permissions', 'BotController@createRoleCategoryPermission');
Route::get('bot/rolecategories/{categorie}/permissions/{permission}', 'BotController@getRoleCategoryPermission');
Route::put('bot/rolecategories/{categorie}/permissions/{permission}', 'BotController@updateRoleCategoryPermission');
Route::delete('bot/rolecategories/{categorie}/permissions/{permission}', 'BotController@deleteRoleCategoryPermission');
=======
>>>>>>> 7825fa41493a2ae4c4324d72e593c91c40f72664
