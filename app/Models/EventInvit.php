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

    public static function add( $args ) {
        $args['status_time'] = date('Y-m-d H:i:s');
        $guest = EventInvit::create($args);
        return $guest;
    }

    public function accept() {
        $this->update([
            'status' => 'accepted',
            'statue_time' => date('Y-m-d H:i:s')
        ]);
        event(new InvitAccepted($this, $this->guild));
    }

    public function refuse() {
        $this->update([
            'status' => 'refused',
            'statue_time' => date('Y-m-d H:i:s')
        ]);
        event(new InvitRefused($this, $this->guild));
    }

    public function cancel() {
        event(new InvitCanceled($this, $this->guild));
        EventInvit::destroy($this->id);
        return false;
    }
}
