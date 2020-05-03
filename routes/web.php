<?php
use App\User;
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
    return redirect('https://discordapp.com/api/oauth2/authorize?client_id='.config('discord.id').'&redirect_uri='.urlencode(config('discord.callback')).'&response_type=code&scope=identify%20email%20guilds');
});
Route::get('/login/discord/callback', 'DiscordController@auth');
Route::get('/logout', function () {
    Auth::logout();
    return redirect('/');
});

Route::get('/', function () {
    if( User::isValid() ) {
        return view('map');
    }
    return view('login');
})->name('home');

Route::get('/status', function () { return view('status'); })->name('status');

Route::get('/ressources/img/pokemon/florked/{name}.png', function ( $name ) {
    $pokemon = \App\Models\Pokemon::where('name_fr', $name)->first();
    if( $pokemon ) {
        $id = $pokemon->pokedex_id;
        $id = ltrim($id, '0');

        $form = \App\Helpers\Helpers::getPokemonFormFromName($name);
        if( $form ) $id .= $form['florked'];

        $remoteImage = 'https://gamepress.gg/pokemongo/sites/pokemongo/files/styles/240w/public/flork-images/'.$id.'.png';
        $imginfo = getimagesize($remoteImage);
        header("Content-type: {$imginfo['mime']}");
        readfile($remoteImage);
    }
})->name('img-pokemon-florked');
