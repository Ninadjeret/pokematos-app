<?php

namespace App;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Guild;
use App\Models\City;
use App\Models\UserGuild;

class User extends Authenticatable
{
     use HasApiTokens, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'guilds'];
    protected $hidden = ['password', 'remember_token',];

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
                $guild_to_add->admin = $userGuild->admin;
                $guilds[] = $guild_to_add;
            }
        }

        return $guilds;
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
                    $city_to_add->admin = $guild->admin;
                    $cities[] = $city_to_add;
                }
            }
        }
        return $cities;
    }

    public function saveGuilds( $guilds ) {
        if( empty( $guilds ) ) return;
        foreach( $guilds as $guild ) {
            $finded_guild = UserGuild::where('user_id', $this->id)
                ->where('guild_id', $guild['id'])
                ->first();
            if( $finded_guild ) {
                $finded_guild->admin = $guild['admin'];
                $finded_guild->save();
            } else {
                UserGuild::create([
                    'guild_id' => $guild['id'],
                    'user_id' => $this->id,
                    'admin' => $guild['admin']
                ]);
            }
        }
    }

}
