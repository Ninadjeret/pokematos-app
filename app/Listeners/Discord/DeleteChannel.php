<?php

namespace App\Listeners\Discord;

use RestCord\DiscordClient;
use App\Events\Events\EventDeleted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class DeleteChannel
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
     * @param  EventDeleted  $event
     * @return void
     */
    public function handle($event)
    {
        $discord = new DiscordClient(['token' => config('discord.token')]);

        if( !empty($event->event->channel_discord_id) ) {
            $channel = $discord->channel->deleteOrcloseChannel([
                'channel.id' => (int) $event->event->channel_discord_id,
            ]);
            $event->event->update(['channel_discord_id' => null]);
        }
    }
}
