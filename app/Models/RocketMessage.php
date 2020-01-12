<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RocketMessage extends Model
{
    protected $table = 'rocket_messages';
    protected $fillable = [
        'guild_id',
        'invasion_id',
        'message_discord_id',
        'channel_discord_id',
        'delete_after_end'
    ];
}
