<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Connector extends Model {
    protected $fillable = [ 'guild_id', 'name', 'channel_discord_id', 'publish', 'filter_gym_type', 'filter_pokemon_type'];
}
