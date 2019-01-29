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
    return redirect('https://discordapp.com/api/oauth2/authorize?client_id=379369796023877632&redirect_uri=http%3A%2F%2F127.0.0.1%3A8000/login/discord/callback&response_type=code&scope=identify%20email');
});
Route::get('/logout', function () {
    Auth::logout();
    return redirect('/login');
});
Route::get('/login/discord/callback', function () {
    $code = $_GET['code'];
    $creds = base64_encode('379369796023877632:3X9XW5XWjEPollAIcyxxXtAvlvKkLdIh');
    $client = new GuzzleHttp\Client();
    $res = $client->post('https://discordapp.com/api/oauth2/token?grant_type=authorization_code&code='.$code.'&redirect_uri=http%3A%2F%2F127.0.0.1%3A8000/login/discord/callback', [
        'headers' => [
            'Authorization' => 'Basic '.$creds,

        ]
    ]);
    echo $res->getStatusCode(); // 200
    echo $res->getBody();
    $data = json_decode($res->getBody());
    $res = $client->get('https://discordapp.com/api/users/@me', [
        'headers' => [
            'Authorization' => 'Bearer '.$data->access_token,
        ]
    ]);
    $user_data = json_decode($res->getBody());
    $user_id = DB::table('users')->where('email', $user_data->email)->first()->id;
    var_dump($user_id);
    Auth::loginUsingId($user_id);
    return redirect('/');
    die();
});
Route::get('/', function () {
    if( Auth::user() ) {
            return view('map');
    }
    return redirect('/login');
});
Route::get('/list', function () {
    return view('list');
});
Route::get('/settings', function () {
    return view('settings');
});
