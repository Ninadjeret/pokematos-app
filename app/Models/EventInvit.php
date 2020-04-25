<?php

namespace App\Models;

use App\Models\Event;
use Illuminate\Database\Eloquent\Model;

class EventInvit extends Model
{
    protected $table = 'event_invits';
    protected $fillable = ['event_id', 'guild_id', 'status', 'status_time', 'discord_channel_id'];
    protected $appends = ['guild'];

    public function getEventAttribute() {
        return Event::find($this->event_id);
    }

    public function getGuildAttribute() {
        return Guild::find($this->guild_id);
    }
}
