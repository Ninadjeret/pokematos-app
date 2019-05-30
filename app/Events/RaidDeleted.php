<?php

namespace App\Events;

use App\Models\Raid;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RaidDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $raid;
    /**
     * Create a new event instance.
     *
     * @return void
     */
     public function __construct( Raid $raid )
     {
         $this->raid = $raid;
     }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
