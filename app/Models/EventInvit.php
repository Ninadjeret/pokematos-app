<?php

namespace App\Models;

use App\Models\Event;
use Illuminate\Database\Eloquent\Model;

class EventInvit extends Model
{
    protected $table = 'event_invits';
    protected $fillable = ['event_id', 'guild_id', 'status', 'status_time', 'channel_discord_id'];
    protected $appends = ['guild'];

    public function event(){
        return $this->belongsTo('App\Models\Event');
    }

    public function getEventAttribute() {
        return Event::find($this->event_id);
    }

    public function getGuildAttribute() {
        return Guild::find($this->guild_id);
    }

    public static function add( $args ) {
        $args['status_time'] = date('Y-m-d H:i:s');
        $guest = EventInvit::create($args);
        $guest->guild->sendAdminMessage('event_invit_sended', [
            '%date'=> date("d/m/Y à H\mi"),
            '%event_name' => $guest->event->name,
            '%guest_name' => $guest->event->guild->name,
        ]);
        return $guest;
    }

    public function accept() {
        $this->update([
            'status' => 'accepted',
            'statue_time' => date('Y-m-d H:i:s')
        ]);
        event(new \App\Events\Events\InvitAccepted($this, $this->guild));
        $this->event->guild->sendAdminMessage('event_invit_accepted', [
            '%date'=> date("d/m/Y à H\mi"),
            '%event_name' => $this->event->name,
            '%guest_name' => $this->guild->name,
        ]);
    }

    public function refuse() {
        $this->update([
            'status' => 'refused',
            'statue_time' => date('Y-m-d H:i:s')
        ]);
        event(new \App\Events\Events\InvitRefused($this, $this->guild));
        $this->event->guild->sendAdminMessage('event_invit_refused', [
            '%date'=> date("d/m/Y à H\mi"),
            '%event_name' => $this->event->name,
            '%guest_name' => $this->guild->name,
        ]);
    }

    public function cancel() {
        event(new \App\Events\Events\InvitCanceled($this, $this->guild));
        $this->guild->sendAdminMessage('event_invit_canceled', [
            '%date'=> date("d/m/Y à H\mi"),
            '%event_name' => $this->event->name,
            '%guest_name' => $this->event->guild->name,
        ]);
        EventInvit::destroy($this->id);
        return false;
    }
}
