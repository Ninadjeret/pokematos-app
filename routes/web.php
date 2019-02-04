<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/login', function () {
    return redirect('https://discordapp.com/api/oauth2/authorize?client_id=379369796023877632&redirect_uri=http%3A%2F%2F127.0.0.1%3A8000/login/discord/callback&response_type=code&scope=identify%20email%20guilds');
});
Route::get('/login/discord/callback', 'DiscordController@auth');
Route::get('/logout', function () {
    Auth::logout();
    return redirect('/');
});

Route::get('/', function () {
    if( Auth::user() ) {
        return view('map');
    }
    return view('login');
})->name('home');

Route::group(['middleware' => ['auth']], function () {
    Route::get('/list', function () {
        return view('list');
    });
    Route::get('/settings', function () {
        return view('settings');
    });
});
