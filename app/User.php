<?php

namespace App;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Guild;
use App\Models\City;
use App\Models\UserGuild;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable
{
     use HasApiTokens, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'guilds', 'discord_id'];
    protected $hidden = ['password', 'remember_token',];
    protected $appends = ['permissions'];

    public static function getPermissions() {
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
            'boss_edit' => [
                'label' => 'Mettre à jour les boss de raid',
                'context' => 'global'
            ],
            'quest_edit' => [
                'label' => 'Mettre à jour les quêtes',
                'context' => 'global'
            ],
            'guild_manage' => [
                'label' => 'Gérer la guild',
                'context' => 'guild'
            ],
        ];
    }

    public function getGuilds() {
        $guilds = [];

        $userGuilds = UserGuild::where('user_id', $this->id)
            ->get();
        if( empty( $userGuilds ) ) {
            return $guilds;
        }

        foreach( $userGuilds as $userGuild ) {
            $guild_to_add = Guild::where('id', $userGuild->guild_id)->first();
            if( $guild_to_add ) {
                $guild_to_add->permissions = $userGuild->permissions;
                $guilds[] = $guild_to_add;
            }
        }

        return $guilds;
    }

    public function getPermissionsAttribute() {
        $permissions = User::getPermissions();
        $userPermissions = [];
        foreach( $this->getGuilds() as $guild ) {
            switch( $guild->permissions ) {
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

    public function can($permission, $context = []) {
        $permissions = User::getPermissions();
        $userPermissions = $this->permissions;

        if( !array_key_exists($permission, $permissions) ) {
            return false;
        }

        if( empty( $userPermissions ) ) {
            return false;
        }

        $permissionContext = $permissions[$permission]['context'];
        switch( $permissionContext ) {
            case 'global':
                foreach( $userPermissions as $guild_id => $guild_permissions ) {
                    $guild = Guild::find($guild_id);
                    if( !$guild ) {
                        continue;
                    }
                    if( in_array( $permission, $guild_permissions ) ) {
                        return true;
                    }
                }
                return false;
                break;
            case 'city':
                foreach( $userPermissions as $guild_id => $guild_permissions ) {
                    $guild = Guild::find($guild_id);
                    if( !$guild ) {
                        continue;
                    }
                    if( $guild->city_id == $context['city_id'] && in_array( $permission, $guild_permissions ) ) {
                        return true;
                    }
                }
                return false;
                break;
            case 'guild':
            foreach( $userPermissions as $guild_id => $guild_permissions ) {
                $guild = Guild::find($guild_id);
                if( !$guild ) {
                    continue;
                }
                if( $guild->id == $context['guild_id'] && in_array( $permission, $guild_permissions ) ) {
                    return true;
                }
            }
            return false;
            break;
        }

        return false;
    }

    public function getCities() {
        $cities = [];
        $cities_ids = [];
        $user_guilds = $this->getGuilds();

        if( empty( $user_guilds ) ) return $cities;

        foreach( $user_guilds as $guild ) {
            if( !in_array($guild->city_id, $cities_ids) ) {
                $cities_ids[] = $guild->city_id;
                $city_to_add = City::find($guild->city_id);
                if( $city_to_add ) {
                    $city_to_add->guilds = [$guild];
                    $city_to_add->permissions = $guild->permissions;
                    $cities[] = $city_to_add;
                }
            } else {
                $city_to_add->guilds[] = $guild;
            }
        }
        return $cities;
    }

    public function saveGuilds( $guilds ) {
        $old_guilds = [];
        if( !empty($guilds) ) {
            foreach( $guilds as $guild ) {
                $finded_guild = UserGuild::where('user_id', $this->id)
                    ->where('guild_id', $guild['id'])
                    ->first();
                if( $finded_guild ) {
                    $finded_guild->admin = $guild['admin'];
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

        Log::debug( print_r($old_guilds, true) );

        $guilds_to_delete = UserGuild::whereNotIn('id', $old_guilds)->get();
        if( !empty($guilds_to_delete) ) {
            foreach( $guilds_to_delete as $guild_to_delete ) {
                UserGuild::destroy($guild_to_delete->id);
            }
        }

    }

}
