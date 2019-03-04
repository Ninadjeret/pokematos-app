<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;
use RestCord\DiscordClient;
use App\User;
use App\Models\Guild;

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

        $res = $this->getDiscordMe($data);
        $res_guilds = $this->getDiscordMeGuilds($data);

        $user_data = json_decode($res->getBody());
        $user_guilds = json_decode($res_guilds->getBody());
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

        //Gestion de l'attribution des droits d'accès
        $auth = false;
        $discord = new DiscordClient(['token' => config('discord.token')]);
        $guilds = [];
        if(  !empty( $user_guilds ) ) {
            foreach( $user_guilds as $user_guild ) {
                $guild = Guild::where( 'discord_id', $user_guild->id )->first();
                if( $guild ) {
                    /*try {
                        $result = $discord->guild->getGuildMember(array(
                            'guild.id' => (float) $guild->discord_id,
                            'user.id' => (float) $user->discord_id,
                        ));

                        //Gestion des droits d'accès
                        if ($result && $guild->access_rule == 'everyone') {
                            $auth = true;
                        } elseif ($result && $guild->access_rule == 'specific_roles' && !empty(array_intersect($guild->authorized_roles, $result->roles))) {
                            $auth = true;
                        }

                        //Gestion des prvilèges d'admin
                        if (!empty($guild->getMapAdminRoles()) && $result && !empty(array_intersect($guild->getMapAdminRoles(), $result->roles))) {
                            $admin[] = $community->wpId;
                        }
                    } catch (Exception $e) {
                        error_log('Exception reçue : ' . $e->getMessage());
                    }*/
                    $auth = true;
                    $guilds[] = [
                        'id' => $guild->id,
                        'admin' => true,
                    ];
                }

            }
        }
        $user->save();
        $user->saveGuilds($guilds);

        //Login
        if( $auth ) {
            Auth::loginUsingId($user->id, true);
            return redirect('/');
        } else {
            return redirect('/?access=denied&code=1');
        }
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

    public function getDiscordMeGuilds( $data ) {
        $client = new Client();
        $res = $client->get('https://discordapp.com/api/users/@me/guilds', [
            'headers' => [
                'Authorization' => 'Bearer '.$data->access_token,
            ]
        ]);
        return $res;
    }
}
