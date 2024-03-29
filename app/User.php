<?php

namespace App;

use App\Models\City;
use App\Models\Guild;
use GuzzleHttp\Client;
use App\Models\UserGuild;
use App\Models\UserAction;
use RestCord\DiscordClient;
use App\Traits\Permissionable;
use App\Core\Rankings\UserRanking;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    //use HasPushSubscriptions;
    use HasApiTokens, Notifiable, Permissionable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'guilds',
        'discord_id',
        'discord_name',
        'discord_access_token',
        'discord_refresh_token',
        'superadmin',
        'ext'
    ];
    protected $hidden = [
        'password',
        'remember_token',
        'discord_access_token',
        'discord_refresh_token',
        'email'
    ];
    protected $appends = [
        'permissions',
        'stats',
    ];
    protected $casts = [
        'superadmin' => 'boolean'
    ];


    public static function initFromBotRequest($request)
    {
        $user_discord_id = $request->user_discord_id;
        $user_name = $request->user_discord_name;

        $user = User::where('discord_id', $user_discord_id)->first();

        if (!$user) {
            $user = User::create([
                'name' => $user_name,
                'password' => Hash::make(str_random(20)),
                'discord_name' => $user_name,
                'discord_id' => $user_discord_id,
            ]);
        }

        return $user;
    }

    public function getGuilds()
    {
        $guilds = [];

        $userGuilds = UserGuild::where('user_id', $this->id)
            ->get();
        if (empty($userGuilds)) {
            return $guilds;
        }

        foreach ($userGuilds as $userGuild) {
            $guild_to_add = Guild::where('id', $userGuild->guild_id)->first();
            if ($guild_to_add) {
                $guild_to_add->permissions = $userGuild->permissions;
                $guilds[] = $guild_to_add;
            }
        }

        return $guilds;
    }

    public function getNickname($guild_id)
    {
        $user_guild = UserGuild::where('user_id', $this->id)->where('guild_id', $guild_id)->first();
        if (!empty($user_guild) && !empty($user_guild->user_nickname)) {
            return $user_guild->user_nickname;
        }
        return $this->name;
    }

    public function getStatsAttribute()
    {
        $stats = ['total' => []];

        $stats['total']['raidCreate'] = UserAction::where('user_id', $this->id)
            ->where('confirmed', 1)
            ->where('type', 'raid-create')
            ->count();

        $stats['total']['raidUpdate'] = UserAction::where('user_id', $this->id)
            ->where('confirmed', 1)
            ->where('type', 'raid-update')
            ->count();

        $stats['total']['questCreate'] = UserAction::where('user_id', $this->id)
            ->where('confirmed', 1)
            ->where('type', 'quest-create')
            ->count();

        return $stats;
    }

    public function getPermissionsAttribute()
    {
        return $this->getCurrentPermissions();
    }

    public function getCities()
    {
        $cities = [];
        $cities_ids = [];
        $user_guilds = $this->getGuilds();
        if (empty($user_guilds)) return $cities;

        foreach ($user_guilds as $guild) {
            if (!in_array($guild->city_id, $cities_ids)) {
                $cities_ids[] = $guild->city_id;
                $city_to_add = City::find($guild->city_id);
                $city_to_add = $city_to_add->toArray();
                if ($city_to_add) {
                    $city_to_add['guilds'] = [$guild];
                    $city_to_add['permissions'] = $guild->permissions;
                    $cities[] = $city_to_add;
                }
            } else {
                foreach ($cities as &$city) {
                    if ($city['id'] == $guild->city_id) {
                        if ($guild->permissions > $city['permissions']) {
                            $city['permissions'] = $guild->permissions;
                        }
                        $city['guilds'][] = $guild;
                    }
                }
            }
        }
        return $cities;
    }

    public function checkGuilds($user_guilds)
    {
        $auth = false;
        $guilds = [];
        $discord = new DiscordClient(['token' => config('discord.token')]);

        //Get all communities acces for super admin
        if ($this->superadmin) {
            $auth = true;
            $allguilds = Guild::where('active', 1)->get();
            if (!empty($allguilds)) {
                foreach ($allguilds as $allguild) {
                    $guilds[] = [
                        'id' => $allguild->id,
                        'permissions' => 30,
                    ];
                }
            }
        }

        //for basic users
        elseif (!empty($user_guilds)) {
            foreach ($user_guilds as $user_guild) {
                $error = 2;
                $auth_discord = false;
                $admin = 0;
                $guild = Guild::where('discord_id', $user_guild->id)
                    ->where('active', 1)
                    ->first();
                if ($guild) {

                    try {
                        $result = $discord->guild->getGuildMember(array(
                            'guild.id' => (int) $guild->discord_id,
                            'user.id' => (int) $this->discord_id,
                        ));

                        if ($result) {

                            //Gestion des droits d'accès
                            if (empty($guild->settings->map_access_rule) || $guild->settings->map_access_rule == 'everyone') {
                                $auth = true;
                                $auth_discord = true;
                            } elseif ($guild->settings->map_access_rule == 'specific_roles' && !empty(array_intersect($guild->settings->map_access_roles, $result->roles))) {
                                $auth_discord = true;
                                $auth = true;
                            } else {
                                $error = 3;
                            }

                            //Gestion des prvilèges de modo
                            if (!empty($guild->settings->map_access_moderation_roles) && !empty(array_intersect($guild->settings->map_access_moderation_roles, $result->roles))) {
                                $admin = 10;
                                $auth = true;
                                $auth_discord = true;
                            }

                            //Gestion des prvilèges d'admin
                            if (!empty($guild->settings->map_access_admin_roles) && !empty(array_intersect($guild->settings->map_access_admin_roles, $result->roles))) {
                                $admin = 30;
                                $auth = true;
                                $auth_discord = true;
                            }

                            //Si l'utilisateur a les permission d'admin sur Discrod, alors il les hérite sur la map
                            if ($user_guild->permissions >= 2146958847) {
                                $admin = 30;
                                $auth = true;
                                $auth_discord = true;
                            }

                            if ($auth_discord) {
                                $guilds[] = [
                                    'id' => $guild->id,
                                    'permissions' => $admin,
                                    'nickname'  => $result->nick
                                ];
                            }
                        }
                    } catch (\Exception $e) {
                        error_log('Exception reçue : ' . $e->getMessage());
                    }
                }
            }
        }

        if ($auth) $error = false;
        $this->saveGuilds($guilds);
        return (object) [
            'auth' => $auth,
            'error' => $error,
        ];
    }

    public function CheckGuildPermissions($guild, $roles, $discord_permissions, $nickname = null)
    {
        $permissions = 0;
        $auth = false;
        if (empty($guild->settings->map_access_rule) || $guild->settings->map_access_rule == 'everyone') {
            $auth = true;
        } elseif ($guild->settings->map_access_rule == 'specific_roles' && !empty(array_intersect($guild->settings->map_access_roles, $roles))) {
            $auth = true;
        }

        //Gestion des prvilèges de modo
        if (!empty($guild->settings->map_access_moderation_roles) && !empty(array_intersect($guild->settings->map_access_moderation_roles, $roles))) {
            $permissions = 10;
            $auth = true;
        }

        //Gestion des prvilèges d'admin
        if (!empty($guild->settings->map_access_admin_roles) && !empty(array_intersect($guild->settings->map_access_admin_roles, $roles))) {
            $permissions = 30;
            $auth = true;
        }

        if ($discord_permissions >= 2146958847) {
            $permissions = 30;
            $auth = true;
        }

        if ($auth) {
            \App\Models\UserGuild::firstOrCreate(
                ['guild_id' => $guild->id, 'user_id' => $this->id],
                ['permissions' => $permissions, 'user_nickname' => $nickname]
            );
        }
    }

    public function saveGuilds($guilds)
    {
        $old_guilds = [];
        if (!empty($guilds)) {
            foreach ($guilds as $guild) {
                $finded_guild = UserGuild::where('user_id', $this->id)
                    ->where('guild_id', $guild['id'])
                    ->first();
                if ($finded_guild) {
                    $finded_guild->permissions = $guild['permissions'];
                    $finded_guild->save();
                } else {
                    $finded_guild = UserGuild::create([
                        'guild_id' => $guild['id'],
                        'user_id' => $this->id,
                        'permissions' => $guild['permissions'],
                    ]);
                }
                if (array_key_exists('nickname', $guild)) {
                    $finded_guild->user_nickname = $guild['nickname'];
                    $finded_guild->save();
                }
                $old_guilds[] = $finded_guild->id;
            }
        }

        $guilds_to_delete = UserGuild::where('user_id', $this->id)
            ->whereNotIn('id', $old_guilds)
            ->get();
        if (!empty($guilds_to_delete)) {
            foreach ($guilds_to_delete as $guild_to_delete) {
                UserGuild::destroy($guild_to_delete->id);
            }
        }
    }

    public static function isValid()
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        $refresh = $user->refreshDiscordToken();
        if (!$refresh) {
            Auth::logout();
            return false;
        }

        sleep(1);
        $res = $user->getDiscordMe();
        $user_data = json_decode($res->getBody());
        $user->update([
            'name' => $user_data->username,
            'discord_name' => $user_data->username,
            'discord_avatar_id' => $user_data->avatar,
        ]);

        $user_guilds = $user->getDiscordMeGuilds();

        if (!$user_guilds) {
            return false;
        }

        $auth = $user->checkGuilds($user_guilds);

        if ($auth) {
            return true;
        } else {
            Auth::logout();
            return false;
        }
    }

    public function refreshDiscordToken()
    {
        $creds = base64_encode(config('discord.id') . ':' . config('discord.secret'));
        $client = new Client();
        /*$res = $client->post('https://discord.com/api/oauth2/token?grant_type=refresh_token&scope=identify%20email%20guilds&refresh_token='.$this->discord_refresh_token.'&redirect_uri='.urlencode(config('discord.callback')), [
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => 'Basic '.$creds,

            ]
        ]);*/

        $res = $client->post('https://discord.com/api/oauth2/token', [
            'http_errors' => false,
            'headers' => [
                'Authorization' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'client_id' => config('discord.id'),
                'client_secret' => config('discord.secret'),
                'grant_type' => 'refresh_token',
                'refresh_token' => $this->discord_refresh_token,
                'redirect_uri' => config('discord.callback'),
                'scope' => 'identify email guilds'
            ]
        ]);

        if ($res->getStatusCode() == '200') {
            $body = json_decode($res->getBody());
            $this->update([
                'discord_access_token' => $body->access_token,
                'discord_refresh_token' => $body->refresh_token,
            ]);
            return $body->access_token;
        }

        return false;
    }

    public function getDiscordMe()
    {
        $client = new Client();
        $res = $client->get('https://discord.com/api/users/@me', [
            'http_errors' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->discord_access_token,
            ]
        ]);
        return $res;
    }

    public function getDiscordMeGuilds()
    {

        $client = new Client();
        $res = $client->get('https://discord.com/api/users/@me/guilds', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->discord_access_token,
            ]
        ]);

        if ($res->getStatusCode() == '200') {
            return json_decode($res->getBody());
        }

        return false;
    }
}
