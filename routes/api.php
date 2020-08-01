<?php

use 
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
    Route::get('user/cities/{city}/gyms', 'UserController@getPOIs');
    Route::post('user/upload', 'UserController@uploadImage');

    Route::get('user/cities/{city}/active-gyms', 'UserController@getActivePOIs');

    Route::get('user/cities/{city}/raids', 'RaidController@getCityRaids');
    Route::post('user/cities/{city}/raids', 'RaidController@create');
    Route::put('user/cities/{city}/raids/{raid}', 'RaidController@create');
    Route::delete('user/cities/{city}/raids/{raid}', 'RaidController@delete');
    Route::get('user/cities/{city}/last-changes', 'CityController@getLastChanges');

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

    Route::get('user/guilds/{guild}/connectors', 'UserController@getConnectors');
    Route::post('user/guilds/{guild}/connectors', 'UserController@createConnector');
    Route::get('user/guilds/{guild}/connectors/{connector}', 'UserController@getConnector');
    Route::put('user/guilds/{guild}/connectors/{connector}', 'UserController@updateConnector');
    Route::delete('user/guilds/{guild}/connectors/{connector}', 'UserController@deleteConnector');

    Route::get('user/guilds/{guild}/questconnectors', 'UserController@getQuestConnectors');
    Route::post('user/guilds/{guild}/questconnectors', 'UserController@createQuestConnector');
    Route::get('user/guilds/{guild}/questconnectors/{connector}', 'UserController@getQuestConnector');
    Route::put('user/guilds/{guild}/questconnectors/{connector}', 'UserController@updateQuestConnector');
    Route::delete('user/guilds/{guild}/questconnectors/{connector}', 'UserController@deleteQuestConnector');

    Route::group(['middleware' => ['can:logs_manage']], function () {
        Route::get('user/guilds/{guild}/logs', 'UserController@getGuildLogs');
        Route::get('user/cities/{city}/logs', 'UserController@getCityLogs');
    });

    Route::get('user/cities/{city}/guilds/{guild}/settings', 'UserController@getGuildOptions');
    Route::put('user/cities/{city}/guilds/{guild}/settings', 'UserController@updateGuildOptions');

    //commun
    Route::get('pokemons', 'PokemonController@getAll');
    Route::get('pokemons/raidbosses', 'PokemonController@getRaidBosses');
    Route::put('pokemons/raidbosses', 'PokemonController@updateRaidBosses');

    Route::get('quests/rewards', 'PokemonController@getQuestRewards');
    Route::get('quests', 'PokemonController@getQuests');
    Route::post('quests', 'PokemonController@createQuest');
    Route::get('quests/{quest}', 'PokemonController@getQuest');
    Route::put('quests/{quest}', 'PokemonController@updateQuest');
    Route::delete('quests/{quest}', 'PokemonController@deleteQuest');

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

    Route::group(['middleware' => ['can:settings_manage']], function () {
        Route::get('settings', 'SettingController@get');
        Route::put('settings', 'SettingController@update');
    });
});

Route::group(['prefix' => 'bot', 'middleware' => ['auth.bot']], function () {

    Route::get('guilds', 'BotController@getGuilds');
    Route::post('guilds', 'BotController@addGuild');

    Route::get('guilds/{guild_id}/roles', 'BotController@getRoles');
    Route::post('guilds/{guild_id}/roles', 'BotController@createRole');
    Route::delete('guilds/{guild_id}/roles/{role}', 'BotController@deleteRole');
    Route::get('guilds/{guild_id}/roles/{role}', 'BotController@getRole');
    Route::put('guilds/{guild_id}/roles/{role}', 'BotController@updateRole');

    Route::get('guilds/{guild_id}/role-categories', 'BotController@getRoleCategories');
    Route::get('guilds/{guild_id}/role-categories/{categorie}', 'BotController@getRoleCategory');
    Route::delete('guilds/{guild_id}/role-categories/{categorie}', 'deleteRoleCategory@getRoleCategory');

    Route::post('raids/imagedecode', 'RaidController@imageDecode');

    //User actions
    Route::group(['middleware' => ['can:guild_access']], function () {
        Route::post('raids', 'RaidController@addRaid');
        Route::post('conversations', 'BotController@addConversation');
        Route::post('events/quiz/answer', 'Bot\Event\Quiz\AnswerController@store');
    });

    //Events
    Route::group(['middleware' => ['can:events_train_check']], function () {
        Route::post('events/train/step/check', 'Bot\Event\Train\StepController@check');
        Route::post('events/train/step/uncheck', 'Bot\Event\Train\StepController@uncheck');
    });
});

Route::get('test', 'Controller@test');
Route::post('debug', 'DebugController@log');

//Stats
Route::get('stats/g/ia', 'StatsController@getGlobalIAReport');
Route::get('stats/c/{city_slug}/ia', 'StatsController@getCityIAReport');

//Public
Route::get('public/pokemons', 'PokemonController@getAll');
Route::get('public/tools/pokemon/get-pokedex-from-name/{name}', 'PokemonController@getPokedexIdFromNameFr');

//Subscriptions
Route::post('subscription', 'SubscriptionController@store');
Route::post('subscription/delete', 'SubscriptionController@destroy');