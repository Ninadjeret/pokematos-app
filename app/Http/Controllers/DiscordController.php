<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;
use App\User;

class DiscordController extends Controller {



    public function auth(Request $request) {

        $code = Input::get('code');
        if( !$code ) {
            return redirect('/?access=cancel');
            die();
        }

        $res = $this->getDiscordToken($code);
        if( $res->getStatusCode() != 200 ) {
            return redirect('/?access=denied&code=1');
            die();
        }

        $data = json_decode($res->getBody());
        $res = $this->getDiscordMe($data);
        if( $res->getStatusCode() != 200 ) {
            return redirect('/?access=denied&code=1');
            die();
        }

        $user_data = json_decode($res->getBody());
        $user = User::where('email',$user_data->email)->first();

        if( $user ) {
            Auth::loginUsingId($user->id);
        } else {
            $user = new User();
        }

        $user->password = Hash::make( str_random(20) );
        $user->name = $user_data->username;
        $user->email = $user_data->email;
        $user->discord_id = $user_data->id;
        $user->discord_name = $user_data->username;
        $user->discord_avatar_id = $user_data->avatar;
        $user->save();
        Auth::loginUsingId($user->id);
        return redirect('/');
        die();

    }

    public function getDiscordToken( $code ) {
        $creds = base64_encode( config('discord.id') . ':' . config('discord.secret') );
        $client = new Client();
        $res = $client->post('https://discordapp.com/api/oauth2/token?grant_type=authorization_code&code='.$code.'&redirect_uri='.urlencode(config('discord.callback')), [
            'headers' => [
                'Authorization' => 'Basic '.$creds,

            ]
        ]);
        return $res;
    }

    public function getDiscordMe( $data ) {
        $client = new Client();
        $res = $client->get('https://discordapp.com/api/users/@me', [
            'headers' => [
                'Authorization' => 'Bearer '.$data->access_token,
            ]
        ]);
        return $res;
    }
}
