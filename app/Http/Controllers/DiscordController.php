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
//use Illuminate\Support\Facades\Input;

class DiscordController extends Controller
{

    public function auth(Request $request)
    {

        $code = \Illuminate\Support\Facades\Request::input('code');
        if (!$code) {
            return redirect('/?access=cancel');
            die();
        }

        $res = $this->getDiscordToken($code);
        if ($res->getStatusCode() != 200) {
            return redirect('/?access=denied&code=1');
            die();
        }

        $data = json_decode($res->getBody());
        $res = $this->getDiscordMe($data);
        if ($res->getStatusCode() != 200) {

            return redirect('/?access=denied&code=1');
            die();
        }


        $res = $this->getDiscordMe($data);
        $res_guilds = $this->getDiscordMeGuilds($data);
        $user_data = json_decode($res->getBody());
        $user_guilds = json_decode($res_guilds->getBody());
        $user = User::where('discord_id', $user_data->id)->first();

        if ($user) {
            //Auth::loginUsingId($user->id);
        } else {
            $user = new User();
        }

        $user->password = Hash::make(str_random(20));
        $user->name = $user_data->username;
        $user->email = $user_data->email;
        $user->discord_id = $user_data->id;
        $user->discord_name = $user_data->username;
        $user->discord_avatar_id = $user_data->avatar;
        $user->discord_discriminator = $user_data->discriminator;
        $user->discord_access_token = $data->access_token;
        $user->discord_refresh_token = $data->refresh_token;
        $user->save();


        //Login
        $result = $user->checkGuilds($user_guilds);
        if ($result->auth) {
            Auth::loginUsingId($user->id, true);
            return redirect('/');
        } else {
            return redirect('/?access=denied&code=' . $result->error);
        }
        die();
    }

    public function getDiscordToken($code)
    {
        $creds = base64_encode(config('discord.id') . ':' . config('discord.secret'));
        $client = new Client();

        $res = $client->post('https://discord.com/api/oauth2/token', [
            'http_errors' => false,
            'headers' => [
                'Authorization' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'client_id' => config('discord.id'),
                'client_secret' => config('discord.secret'),
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => config('discord.callback'),
                'scope' => 'identify email guilds'
            ]
        ]);
        /*$res = $client->post('https://discord.com/api/oauth2/token?grant_type=authorization_code&code='.$code.'&redirect_uri='.urlencode(config('discord.callback')), [
            'http_errors' => false,
            'headers' => [
                'Authorization' => 'Basic '.$creds,
            ],
        ]); */
        return $res;
    }

    public function getDiscordMe($data)
    {
        $client = new Client();
        $res = $client->get('https://discord.com/api/users/@me', [
            'http_errors' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . $data->access_token,
            ]
        ]);
        return $res;
    }

    public function getDiscordMeGuilds($data)
    {
        $client = new Client();
        $res = $client->get('https://discord.com/api/users/@me/guilds', [
            'http_errors' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . $data->access_token,
            ]
        ]);
        return $res;
    }

    public function getRoles(Request $request, City $city, Guild $guild)
    {
        return response()->json($guild->getDiscordRoles(), 200);
    }

    public function getChannels(Request $request, City $city, Guild $guild)
    {
        $discord = new DiscordClient(['token' => config('discord.token')]);
        $channels = $discord->guild->getGuildChannels(['guild.id' => (int) $guild->discord_id]);

        $return = [];
        foreach ($channels as $channel) {
            if ($channel->type == 0) {
                $return[] = [
                    'name' => $channel->name,
                    'id' => (string) $channel->id
                ];
            }
        }

        return response()->json($return, 200);
    }

    public function getChannelCategories(Request $request, City $city, Guild $guild)
    {
        $discord = new DiscordClient(['token' => config('discord.token')]);
        $channels = $discord->guild->getGuildChannels(['guild.id' => (int) $guild->discord_id]);

        $return = [];
        foreach ($channels as $channel) {
            if ($channel->type == 4) {
                $return[] = [
                    'name' => $channel->name,
                    'id' => (string) $channel->id
                ];
            }
        }

        return response()->json($return, 200);
    }
}