<?php

namespace App\Events\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class TrainStepUnchecked
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $train_step;
    public $train;
    public $event;
    public $guild;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($train_step, $train, $event, $guild)
    {
        $this->train_step = $train_step;
        $this->train = $train;
        $this->event = $event;
        $this->guild = $guild;
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
