<?php

use Illuminate\Support\Facades\Route;


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


Route::post('raids', 'Bot\Raids\RaidController@store');
Route::post('raids/channel', 'Bot\Raids\ChannelController@store');
Route::post('raids/participant', 'Bot\Raids\ParticipantController@store');
Route::delete('raids/participant', 'Bot\Raids\ParticipantController@destroy');
Route::post('conversations', 'Bot\ConversationController@store');
Route::post('events/quiz/answer', 'Bot\Event\Quiz\AnswerController@store');


//Events
Route::group(['middleware' => ['can:events_train_check']], function () {
  Route::post('events/train/step/check', 'Bot\Event\Train\StepController@check');
  Route::post('events/train/step/uncheck', 'Bot\Event\Train\StepController@uncheck');
});