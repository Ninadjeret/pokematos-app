<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
    Route::get('user/guilds', 'App\Guilds\GuildController@index');
    Route::get('user/guilds/{guild}', 'App\Guilds\GuildController@show');

    Route::post('user/upload', 'UserController@uploadImage');

    //City
    Route::put('user/cities/{city}', 'UserController@updateCity');

    //Quests
    Route::post('user/cities/{city}/quests', 'UserController@createQuest');
    Route::put('user/cities/{city}/quests/{questInstance}', 'UserController@updateQuest');
    Route::delete('user/cities/{city}/quests/{questInstance}', 'UserController@deleteQuest');

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
    Route::get('user/cities/{city}/guilds/{guild}/channels', 'DiscordController@getChannels');
    Route::get('user/cities/{city}/guilds/{guild}/channelcategories', 'DiscordController@getChannelCategories');

    Route::group(['middleware' => ['can:rocket_bosses_edit']], function () {
        Route::get('user/cities/{city}/guilds/{guild}/settings', 'UserController@getGuildOptions');
        Route::put('user/cities/{city}/guilds/{guild}/settings', 'UserController@updateGuildOptions');
    });

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

    Route::group(['middleware' => ['can:logs_manage']], function () {
        Route::get('user/guilds/{guild}/logs', 'UserController@getGuildLogs');
        Route::get('user/cities/{city}/logs', 'UserController@getCityLogs');
    });

    Route::get('user/cities/{city}/guilds/{guild}/settings', 'UserController@getGuildOptions');
    Route::put('user/cities/{city}/guilds/{guild}/settings', 'UserController@updateGuildOptions');

    //commun
    Route::get('pokemons/raidbosses', 'PokemonController@getRaidBosses');
    Route::put('pokemons/raidbosses', 'PokemonController@updateRaidBosses');

    //Rocket
    Route::get('rocket/bosses', 'RocketController@getBosses');
    Route::put('rocket/bosses/{boss}', 'RocketController@updateBoss');
    Route::post('user/cities/{city}/rocket/invasions', 'RocketController@createInvasion');
    Route::put('user/cities/{city}/rocket/invasions/{invasion}', 'RocketController@updateInvasion');
    Route::delete('user/cities/{city}/rocket/invasions/{invasion}', 'RocketController@deleteInvasion');

    Route::get('user/guilds/{guild}/invasionconnectors', 'RocketController@getConnectors');
    Route::post('user/guilds/{guild}/invasionconnectors', 'RocketController@createConnector');
    Route::get('user/guilds/{guild}/invasionconnectors/{connector}', 'RocketController@getConnector');
    Route::put('user/guilds/{guild}/invasionconnectors/{connector}', 'RocketController@updateConnector');
    Route::delete('user/guilds/{guild}/invasionconnectors/{connector}', 'RocketController@deleteConnector');

    //Events
    Route::get('user/cities/{city}/events', 'EventController@getActiveEvents');
    Route::get('user/cities/{city}/events/{event}', 'EventController@getEvent');

    Route::post('user/guilds/{guild}/events', 'EventController@createEvent');
    Route::post('user/guilds/{guild}/events/{event}/clone', 'EventController@cloneEvent');
    Route::put('user/guilds/{guild}/events/{event}/steps', 'EventController@updateSteps');
    Route::put('user/guilds/{guild}/events/{event}', 'EventController@updateEvent');
    Route::delete('user/guilds/{guild}/events/{event}', 'EventController@deleteEvent');
    Route::get('user/guilds/{guild}/events', 'EventController@getGuildEvents');
    Route::get('user/guilds/{guild}/events/guilds', 'EventController@getGuestableGuilds');
    Route::get('user/guilds/{guild}/events/invits', 'EventController@getInvits');
    Route::post('user/guilds/{guild}/events/invits/{invit}/accept', 'EventController@acceptInvit');
    Route::post('user/guilds/{guild}/events/invits/{invit}/refuse', 'EventController@refuseInvit');
    Route::post('user/guilds/{guild}/events/{event}/steps/{step}/check', 'EventController@checkStep');
    Route::post('user/guilds/{guild}/events/{event}/steps/{step}/uncheck', 'EventController@uncheckStep');
    Route::get('events/quiz/themes', 'EventController@getThemes');
    Route::get('events/quiz/available-questions', 'Events\QuizController@getAvailableQuestions');

    Route::group([
        'prefix' => 'quiz',
        'middleware' => ['can:quiz_manage']
    ], function () {
        Route::resource('questions', 'Events\QuizQuestionController');
        Route::resource('themes', 'Events\QuizThemeController');
    });

});


/**
 * USER ROUTES
 */
Route::group([
    'prefix' => 'user',
    'middleware' => ['auth:api'],
], function ($router) {
    require base_path('routes/api/user.php');
});


/**
 * BOT ROUTES
 */
Route::group([
    'prefix' => 'bot',
    'middleware' => ['auth.bot'],
], function ($router) {
    require base_path('routes/api/bot.php');
});


/**
 * EXTERNAL ROUTES
 */
Route::group([
    'prefix' => 'ext/v1',
    'middleware' => ['auth.ext'],
], function ($router) {
    require base_path('routes/api/ext.v1.php');
});


/**
 * OTHER ROUTES
 */
Route::get('test', 'Controller@test');
Route::post('debug', 'DebugController@log');

//Stats
Route::get('stats/g/ia', 'StatsController@getGlobalIAReport');
Route::get('stats/c/{city_slug}/ia', 'StatsController@getCityIAReport');

//Monitoring
Route::get('monitor/analyzer', 'StatsController@monitorAnalyzer');
Route::get('monitor/status', 'StatsController@monitorStatus');

//Public
Route::get('public/pokemons', 'App\Pokemon\PokemonController@index');
Route::get('public/tools/pokemon/get-pokedex-from-name/{name}', 'Tools\PokemonController@getPokedexIdFromNameFr');

//Subscriptions
Route::post('subscription', 'SubscriptionController@store');
Route::post('subscription/delete', 'SubscriptionController@destroy');
