<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use App\models\Guild;
use App\models\Stop;
use App\models\RoleCategory;
use RestCord\DiscordClient;
use Illuminate\Support\Facades\Log;

class Role extends Model {

    protected $fillable = ['discord_id', 'guild_id', 'name', 'type', 'gym_id', 'zone_id', 'pokemon_id', 'restricted', 'category_id'];
    protected $appends = ['guild', 'category'];
    protected $hidden = ['guild_id'];
    protected $casts = [
        'restricted' => 'boolean'
    ];

    public function getGuildAttribute() {
        return Guild::find($this->guild_id);
    }

    public function getCategoryAttribute() {
        return RoleCategory::find($this->category_id);
    }

    public static function add($args) {

        $guild = Guild::find($args['guild_id']);
        $roleCategory = RoleCategory::find($args['category_id']);

        $color = 0;
        switch ($args['type']) {
            case 'gym':
                $gym = Stop::find($args['gym_id']);
                $color = ( $gym->ex ) ? hexdec($guild->settings->roles_gymex_color) : hexdec($guild->settings->roles_gym_color) ;
                break;
            case 'zone':
                $color = hexdec($guild->settings->roles_zone_color);
                break;
            case 'pokemon':
                $color = hexdec($guild->settings->roles_pokemon_color);
                break;
        }

        $discord = new DiscordClient(['token' => config('discord.token')]);
        $discord_role = $discord->guild->createGuildRole([
            'guild.id' => (int) $guild->discord_id,
            'name' => $args['name'],
            'mentionable' => true,
            'color' => $color,
        ]);

        $role = Role::create([
            'discord_id' => $discord_role->id,
            'guild_id' => $guild->id,
            'category_id' => $roleCategory->id,
            'name' => $args['name'],
            'type' => $args['type'],
            'gym_id' => $args['gym_id'],
            'zone_id' => $args['zone_id'],
            'pokemon_id' => $args['pokemon_id'],
        ]);

        /*$discord->channel->createMessage([
            'channel.id' => (int) $roleCategory->channel_discord_id,
            'content' => '<@&'.$role->discord_id.'>'
        ]);*/

        return $role;
    }

    public function change($args) {

        $guild = Guild::find($this->guild_id);

        $type = (isset($args['type'])) ? $args['type'] : $this->type;
        $name = (isset($args['name'])) ? $args['name'] : $this->name;
        $gym_id = (isset($args['gym_id'])) ? $args['gym_id'] : $this->gym_id;
        $zone_id = (isset($args['zone_id'])) ? $args['zone_id'] : $this->zone_id;
        $pokemon_id = (isset($args['pokemon_id'])) ? $args['pokemon_id'] : $this->pokemon_id;
        $category_id = (isset($args['category_id'])) ? $args['category_id'] : $this->category_id;

        $color = 0;
        switch ($type) {
            case 'gym':
                $gym = Stop::find($gym_id);
                $color = ( $gym->ex ) ? hexdec($guild->settings->roles_gymex_color) : hexdec($guild->settings->roles_gym_color) ;
                break;
            case 'zone':
                $color = hexdec($guild->settings->roles_zone_color);
                break;
            case 'pokemon':
                $color = hexdec($guild->settings->roles_pokemon_color);
                break;
        }

        $discord = new DiscordClient(['token' => config('discord.token')]);
        $discord_role = $discord->guild->modifyGuildRole([
            'guild.id' => (int) $this->guild->discord_id,
            'role.id' => (int) $this->discord_id,
            'name' => $name,
            'color' => $color,
        ]);

        $this->update([
            'name' => $name,
            'type' => $type,
            'category_id' => $category_id,
            'gym_id' => $gym_id,
            'zone_id' => $zone_id,
            'pokemon_id' => $pokemon_id,
        ]);

        return true;
    }

    public function suppr() {

        $discord = new DiscordClient(['token' => config('discord.token')]);
        $discord_role = $discord->guild->deleteGuildRole([
            'guild.id' => (int) $this->guild->discord_id,
            'role.id' => (int) $this->discord_id
        ]);

        Role::destroy($this->id);

        return true;
    }

}
