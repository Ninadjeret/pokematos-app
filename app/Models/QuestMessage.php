<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestMessage extends Model
{
    protected $table = 'quest_messages';
    protected $fillable = ['guild_id', 'quest_instance_id', 'message_discord_id', 'channel_discord_id' ];
}
