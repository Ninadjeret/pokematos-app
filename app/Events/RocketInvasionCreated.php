<?php

namespace App\Events;

use App\Models\UserAction;
use App\Models\RocketInvasion;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RocketInvasionCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $invasion;
    public $announce;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(RocketInvasion $invasion, UserAction $announce)
    {
        $this->invasion = $invasion;
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