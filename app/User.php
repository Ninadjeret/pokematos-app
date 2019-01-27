<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Guild;
use App\Models\City;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function getGuilds() {
        $guilds = array();
        if( empty( $this->guilds ) ) return $guilds;
        $guilds_decoded = json_decode($this->guilds);
        if( empty( $guilds_decoded ) ) return $guilds;

        foreach( $guilds_decoded as $guild_id ) {
            $guild_to_add = Guild::where('guild_id', $guild_id)->first();
            if( $guild_to_add ) {
                $guilds[] = $guild_to_add;
            }
        }

        return $guilds;
    }

    public function getCities() {
        $cities = array();
        $user_guilds = $this->getGuilds();

        if( empty( $user_guilds ) ) return $cities;

        foreach( $user_guilds as $guild ) {
            $cities[] = City::find($guild->id);
        }
        return $cities;
    }
}
