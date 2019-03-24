<?php

namespace App\models;

use App\Models\City;
use App\Models\GuildSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;

class Guild extends Model
{
    protected $fillable = ['authorized_roles',];
    protected $hidden = ['city_id'];
    protected $appends = ['city', 'settings'];
    protected $casts = [
        'authorized_roles' => 'array',
    ];

    protected $allowedSettings = [
        'map_access_rule' => 'everyone',
        'map_access_roles' => [],
        'map_access_admin_roles' => [],

        'roles_gym_color' => '#009688',
        'roles_gymex_color' => '#E91E63',
        'roles_zone_color' => '#2196F3',
        'roles_pokemon_color' => '#4CAF50',
    ];

    public function getCityAttribute() {
        return City::find($this->city_id);
    }

    public function getSettingsAttribute() {
        $return = [];
        $settings = GuildSetting::where('guild_id', $this->id)->get();

        foreach( $this->allowedSettings as $settingKey => $value ) {
            if( $settings ) {
                foreach( $settings as $setting ) {
                    if($setting->key == $settingKey) $value = ( json_decode($setting->value) ) ? json_decode($setting->value) : $setting->value ;
                }
            }
            $return[$settingKey] = $value;
        }
        return (object) $return;
    }

    public function updateSettings( $settings ) {
        foreach( $settings as $key => $value ) {

            if( is_array($value) ) $value = json_encode($value);

            $setting = GuildSetting::where('guild_id', $this->id)
                ->where('key', $key)
                ->first();
            if( !empty($setting) ) {
                $setting->update(['value' => $value]);
            } else {
                $setting = GuildSetting::create([
                    'guild_id' => $this->id,
                    'key' => $key,
                    'value' => $value
                ]);
            }
        }
        return true;
    }

}
