<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class raidChannel extends Model {

    protected $table = 'raid_channels';
    protected $fillable = ['guild_id', 'raid_id', 'channel_discord_id'];

}
