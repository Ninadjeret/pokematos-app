<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Guild;
use App\Models\City;
use GuzzleHttp\Client;
use App\Models\UserAction;
use App\Models\UserGuild;
use RestCord\DiscordClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use NotificationChannels\WebPush\HasPushSubscriptions;

class User extends Authenticatable
{
    //use HasPushSubscriptions;
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'guilds',
        'discord_id',
        'discord_access_token',
        'discord_refresh_token',
        'superadmin'
    ];
    protected $hidden = [
        'password',
        'remember_token',
        'discord_access_token',
        'discord_refresh_token'
    ];
    protected $appends = [
        'permissions',
        'stats'
    ];
    protected $casts = [
        'superadmin' => 'boolean'
    ];

    public static function getPermissions()
    {
        return [
            'raid_delete' => [
                'label' => 'Supprimer des raids',
                'context' => 'city'
            ],
            'raidex_add' => [
                'label' => 'Annoncer des Raids EX',
                'context' => 'city'
            ],
            'poi_edit' => [
                'label' => 'Gérer les POIs',
                'context' => 'city'
            ],
            'zone_edit' => [
                'label' => 'Gérer les zones',
                'context' => 'city'
            ],
            'logs_manage' => [
                'label' => 'Gérer les logs',
                'context' => 'city'
            ],
            'boss_edit' => [
                'label' => 'Mettre à jour les boss de raid',
                'context' => 'global'
            ],
            'quest_edit' => [
                'label' => 'Mettre à jour les quêtes',
                'context' => 'global'
            ],
            'rocket_bosses_edit' => [
                'label' => 'Mettre à jour les Boss Rocket',
                'context' => 'global'
            ],
            'guild_manage' => [
                'label' => 'Gérer la guild',
                'context' => 'guild'
            ],
            'events_manage' => [
                'label' => 'Gérer les évents',
                'context' => 'guild'
            ],
            'events_train_check' => [
                'label' => 'Gérer l\'avancement d\'un pokétrain',
                'context' => 'guild'
            ],
        ];
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
        $permissions = User::getPermissions();
        $userPermissions = [];
        foreach ($this->getGuilds() as $guild) {
            switch ($guild->permissions) {
                case 30:
                    $userPermissions[$guild->id] = array_keys($permissions);
                    break;
                case 20:
                    $userPermissions[$guild->id] = array_keys($permissions);
                    break;
                case 10:
                    $userPermissions[$guild->id] = $guild->settings->access_moderation_permissions;
                    break;
                case 0:
                    $userPermissions[$guild->id] = [];
                    break;
            }
        }

        return $userPermissions;
    }

    public function can($permission, $context = [])
    {
        $permissions = User::getPermissions();
        $userPermissions = $this->permissions;

        if (!array_key_exists($permission, $permissions)) {
            return false;
        }

        if (empty($userPermissions)) {
            return false;
        }

        $permissionContext = $permissions[$permission]['context'];
        switch ($permissionContext) {
            case 'global':
                foreach ($userPermissions as $guild_id => $guild_permissions) {
                    $guild = Guild::find($guild_id);
                    if (!$guild) {
                        continue;
                    }
                    if (in_array($permission, $guild_permissions)) {
                        return true;
                    }
                }
                return false;
                break;
            case 'city':
                foreach ($userPermissions as $guild_id => $guild_permissions) {
                    $guild = Guild::find($guild_id);
                    if (!$guild) {
                        continue;
                    }
                    if ($guild->city_id == $context['city_id'] && in_array($permission, $guild_permissions)) {
                        return true;
                    }
                }
                return false;
                break;
            case 'guild':
                foreach ($userPermissions as $guild_id => $guild_permissions) {
                    $guild = Guild::find($guild_id);
                    if (!$guild) {
                        continue;
                    }
                    if ($guild->id == $context['guild_id'] && in_array($permission, $guild_permissions)) {
                        return true;
                    }
                }
                return false;
                break;
        }

        return false;
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
                                ];
                            }
                        }
                    } catch (Exception $e) {
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
                        'permissions' => $guild['permissions']
                    ]);
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