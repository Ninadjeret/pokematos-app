<?php

namespace App\Models;

use App\Core\Discord\Discord;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;

class DiscordMessage extends Model
{
    protected $fillable = ['relation_type', 'relation_id', 'guild_id', 'discord_id', 'channel_discord_id', 'type', 'connector_id', 'to_delete_at'];

    public function relation()
    {
        return $this->morphTo();
    }

    public function getGuildAttribute()
    {
        return Guild::find($this->guild_id);
    }

    public function suppr()
    {
        $result = Discord::deleteMessage([
            'channel.id' => (int) $this->channel_discord_id,
            'message.id' => (int) $this->discord_id
        ]);
        if ($result) {
            $this->delete();
        } else {
            $this->to_delete_at = date('Y-m-d H:i:s');
        }
    }
}
