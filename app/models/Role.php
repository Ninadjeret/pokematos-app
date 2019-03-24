<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use App\models\Guild;
use App\models\RoleCategory;
use RestCord\DiscordClient;


class Role extends Model {

    protected $fillable = ['discord_id', 'guild_id', 'name', 'type', 'relation_id', 'restricted'];
    protected $appends = ['guild'];
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

        $discord = new DiscordClient(['token' => config('discord.token')]);
        $discord_role = $discord->guild->createGuildRole([
            'guild.id' => (int) $guild->discord_id,
            'name' => $args['name'],
            'mentionable' => true,
        ]);

        $role = Role::create([
            'discord_id' => $discord_role->id,
            'guild_id' => $guild->id,
            'category_id' => $roleCategory->id,
            'name' => $args['name'],
            'type' => $args['type'],
            'relation_id' => $args['relation_id'],
        ]);

        /*$discord->channel->createMessage([
            'channel.id' => (int) $roleCategory->channel_discord_id,
            'content' => '<@&'.$role->discord_id.'>'
        ]);*/

        return $role;
    }

    public function change($args) {

        $discord = new DiscordClient(['token' => config('discord.token')]);
        $discord_role = $discord->guild->modifyGuildRole([
            'guild.id' => (int) $this->guild->discord_id,
            'role.id' => (int) $this->discord_id,
            'name' => ($args['name']) ? $args['name'] : $this->name,
        ]);

        $this->update([
            'name' => ($args['name']) ? $args['name'] : $this->name,
            'category_id' => ($args['category_id']) ? $args['category_id'] : $this->category_id,
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
