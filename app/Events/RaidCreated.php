<?php

namespace App\Events;

use App\Models\Raid;
use App\Models\UserAction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RaidCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $raid;
    public $announce;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct( Raid $raid, UserAction $announce )
    {
        $this->raid = $raid;
        $this->announce = $announce;
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
