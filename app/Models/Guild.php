<?php

namespace App\Models;

use App\Models\City;
use GuzzleHttp\Client;
use RestCord\DiscordClient;
use App\Models\GuildSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;

class Guild extends Model
{
    protected $fillable = [
        'token',
        'discord_id',
        'name',
        'type',
        'city_id',
        'active',
    ];
    protected $hidden = ['city_id'];
    protected $appends = ['city', 'settings', 'watched_channels'];
    protected $casts = [
        'authorized_roles' => 'array',
    ];

    protected $allowedSettings = [
        'map_access_rule' => ['default' => 'everyone', 'type' => 'string'],
        'map_access_roles' => ['default' => [], 'type' => 'array'],
        'map_access_admin_roles' => ['default' => [], 'type' => 'array'],
        'map_access_moderation_roles' => ['default' => [], 'type' => 'array'],
        'access_moderation_permissions' => ['default' => [], 'type' => 'array'],

        'roles_forbidden_message' => ['default' => 'Ahum, merci de ne pas mentionner ce role ici', 'type' => 'string'],

        'raidsex_active' => ['default' => false, 'type' => 'boolean'],
        'raidsex_channels' => ['default' => false, 'type' => 'boolean'],
        'raidsex_channel_category_id' => ['default' => '', 'type' => 'string'],
        'raidsex_access_everyone' => ['default' => true, 'type' => 'boolean'],

        'raidreporting_images_active' => ['default' => false, 'type' => 'boolean'],
        'raidreporting_images_delete' => ['default' => false, 'type' => 'boolean'],
        'raidreporting_text_active' => ['default' => false, 'type' => 'boolean'],
        'raidreporting_text_delete' => ['default' => false, 'type' => 'boolean'],
        'raidreporting_text_prefixes' => ['default' => ["+raid","+Raid"], 'type' => 'array'],
        'raidreporting_channel_type' => ['default' => '', 'type' => 'string'],
        'raidreporting_channel_discord_id' => ['default' => '', 'type' => 'string'],
        'raidreporting_gym_min_proability' => ['default' => 70, 'type' => 'integer'],

        'welcome_active' => ['default' => false, 'type' => 'boolean'],
        'welcome_message' => ['default' => 'Bienvenue {utilisateur}, nous sommes ravis de te voir ici !', 'type' => 'string'],
        'welcome_channel_discord_id' => ['default' => false, 'type' => 'string'],
    ];

    public function getCityAttribute() {
        return City::find($this->city_id);
    }

    public function getWatchedChannelsAttribute() {
        $watched_channels = RoleCategory::where('guild_id', $this->id)->get();
        $return = [];
        foreach( $watched_channels as $watched_channel ) {
            if( !in_array( $watched_channel->channel_discord_id, $return ) && !empty($watched_channel->channel_discord_id) ) {
                $return[] = $watched_channel->channel_discord_id;
            }
        }
        return $return;
    }

    public function getSettingsAttribute() {
        $return = [];
        $settings = GuildSetting::where('guild_id', $this->id)->get();

        foreach( $this->allowedSettings as $settingKey => $setting_data ) {
            $return[$settingKey] = $setting_data['default'];
            if( $settings ) {
                foreach( $settings as $setting ) {
                    if($setting->key == $settingKey) {
                        switch($setting_data['type']) {
                            case 'string':
                                $return[$settingKey] = $setting->value;
                                break;
                                case 'integer':
                                    $return[$settingKey] = (int) $setting->value;
                                    break;
                            case 'boolean':
                                $return[$settingKey] = (boolean) $setting->value;
                            case 'array':
                                $return[$settingKey] = json_decode($setting->value);
                        }
                    }
                }
            }
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

        //On avertit le bot de la MAJ
        $client = new Client();
        $url = config('app.bot_sync_url');
        if( !empty($url) ) {
            $res = $client->get($url);
        }

        return true;
    }

    public function getDiscordRoles() {
        $discord = new DiscordClient(['token' => config('discord.token')]);
        $roles = $discord->guild->getGuildRoles(['guild.id' => (int) $this->discord_id]);
        foreach( $roles as &$role ) {
            $role->id = (string) $role->id;
        }
        return $roles;
    }

}
