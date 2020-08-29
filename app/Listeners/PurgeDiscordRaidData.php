<?php

namespace App\Listeners;

use App\Events\RaidDeleted;
use RestCord\DiscordClient;
use App\Models\RaidMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class PurgeDiscordRaidData
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  RaidDeleted  $event
     * @return void
     */
    public function handle($event)
    {

        if ($event instanceof \App\Events\RaidDeleted || $event instanceof \App\Events\RaidEnded) {
            $event->raid->channels()->get()->each(function ($channel) {
                $channel->suppr();
            });

            $event->raid->messages()->get()->each(function ($message) {
                $message->suppr();
            });
        }

        if ($event instanceof \App\Events\RaidUpdated) {
            $event->raid->messages()->get()->each(function ($message) {
                $message->suppr();
            });
        }
    }
}