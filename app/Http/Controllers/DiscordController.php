<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\Role;
use App\Models\City;
use App\Models\Guild;
use GuzzleHttp\Client;
use RestCord\DiscordClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

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
        Log::debug( print_r( $data, true ) );
        $res = $this->getDiscordMe($data);
        if( $res->getStatusCode() != 200 ) {
            return redirect('/?access=denied&code=1');
            die();
        }

        $res = $this->getDiscordMe($data);
        $res_guilds = $this->getDiscordMeGuilds($data);

        $user_data = json_decode($res->getBody());
        Log::debug( print_r( $user_data, true ) );
        $user_guilds = json_decode($res_guilds->getBody());
        $user = User::where('discord_id',$user_data->id)->first();

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
        $user->discord_discriminator = $user_data->discriminator;
        $user->discord_access_token = $data->access_token;
        $user->discord_refresh_token = $data->refresh_token;

        //Gestion de l'attribution des droits d'accès
        $auth = false;
        $discord = new DiscordClient(['token' => config('discord.token')]);
        $guilds = [];
        if(  !empty( $user_guilds ) ) {
            foreach( $user_guilds as $user_guild ) {

                $auth_discord = false;
                $admin = 0;
                $guild = Guild::where( 'discord_id', $user_guild->id )
                    ->where('active', 1)
                    ->first();
                if( $guild ) {

                    try {
                        $result = $discord->guild->getGuildMember(array(
                            'guild.id' => (int) $guild->discord_id,
                            'user.id' => (int) $user->discord_id,
                        ));

                        if( $result ) {

                            //Gestion des droits d'accès
                            if( empty($guild->settings->map_access_rule) || $guild->settings->map_access_rule == 'everyone' ) {
                                $auth = true;
                                $auth_discord = true;
                                Log::debug(print_r($guild->name, true));
                            } elseif( $guild->settings->map_access_rule == 'specific_roles' && !empty(array_intersect($guild->settings->map_access_roles, $result->roles))) {
                                $auth_discord = false;
                                $auth = true;
                            }

                            //Gestion des prvilèges de modo
                            if ( !empty($guild->settings->map_access_moderation_roles) && !empty(array_intersect($guild->settings->map_access_moderation_roles, $result->roles))) {
                                $admin = 10;
                            }

                            //Gestion des prvilèges d'admin
                            if ( !empty($guild->settings->map_access_admin_roles) && !empty(array_intersect($guild->settings->map_access_admin_roles, $result->roles))) {
                                $admin = 20;
                            }

                            //Si l'utilisateur a les permission d'admin sur Discrod, alors il les hérite sur la map
                            if( $user_guild->permissions >= 2146958847 ) {
                                $admin = 30;
                            }

                            if( $auth_discord ) {
                                $guilds[] = [
                                    'id' => $guild->id,
                                    'permissions' => $admin,
                                ];
                            }


                        }

                        //Gestion des prvilèges d'admin
                        /***/
                    } catch (Exception $e) {
                        error_log('Exception reçue : ' . $e->getMessage());
                    }


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

    public function getRoles( Request $request, City $city, Guild $guild ) {
        return response()->json($guild->getDiscordRoles(), 200);
    }

    public function getChannels( Request $request, City $city, Guild $guild ) {
        $discord = new DiscordClient(['token' => config('discord.token')]);
        $channels = $discord->guild->getGuildChannels(['guild.id' => $guild->discord_id]);

        $return = [];
        foreach($channels as $channel) {
            if( $channel->type == 0 ) {
                $return[] = [
                    'name' => $channel->name,
                    'id' => (string) $channel->id
                ];
            }
        }

        return response()->json($return, 200);
    }

    public function getChannelCategories( Request $request, City $city, Guild $guild ) {
        $discord = new DiscordClient(['token' => config('discord.token')]);
        $channels = $discord->guild->getGuildChannels(['guild.id' => $guild->discord_id]);

        $return = [];
        foreach($channels as $channel) {
            if( $channel->type == 4 ) {
                $return[] = [
                    'name' => $channel->name,
                    'id' => (string) $channel->id
                ];
            }
        }

        return response()->json($return, 200);
    }
}
