<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RaidMessage extends Model
{
    protected $table = 'raid_messages';
    protected $fillable = ['guild_id', 'raid_id', 'message_discord_id', 'channel_discord_id', 'delete_after_end' ];
}
