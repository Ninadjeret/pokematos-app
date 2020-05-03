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
    protected $appends = ['city', 'settings', 'watched_channels', 'event_channels'];
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

        'events_create_channels' => ['default' => false, 'type' => 'boolean'],
        'events_channel_discord_id' => ['default' => false, 'type' => 'string'],
        'events_trains_add_messages' => ['default' => false, 'type' => 'boolean'],
        'events_trains_message_check' => ['default' => 'Nous passons à la prochaine étape : {next_etape_nom}. RDV à {next_etape_heure}', 'type' => 'string'],
        'events_accept_invits' => ['default' => true, 'type' => 'boolean'],

        'comadmin_active' => ['default' => false, 'type' => 'boolean'],
        'comadmin_channel_discord_id' => ['default' => false, 'type' => 'string'],
        'comadmin_types' => ['default' => [], 'type' => 'array'],
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

    public function getEventChannelsAttribute() {

        $events = \App\Models\Event::where('guild_id', $this->id)
            //->where('status', 'active')
            ->get();

        $invits = \App\Models\EventInvit::where('status', 'accepted')->where('guild_id', $this->id)->get();

        $return = [];
        foreach( $events as $event ) {
            if( !in_array( $event->channel_discord_id, $return ) && !empty($event->channel_discord_id) ) {
                $return[] = $event->channel_discord_id;
            }
        }
        foreach( $invits as $invit ) {
            if( !in_array( $invit->channel_discord_id, $return ) && !empty($invit->channel_discord_id) ) {
                $return[] = $invit->channel_discord_id;
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
        \App\Core\Discord::SyncBot();

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

    public function sendAdminMessage( $type, $args ){
        if( !$this->settings->comadmin_active || !in_array($type, $this->settings->comadmin_types) ) return;
        \App\Core\Conversation::sendToDiscord($this->settings->comadmin_channel_discord_id, $this, 'admin', $type, $args);
    }

}
