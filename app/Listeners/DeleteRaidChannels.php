<?php

namespace App\Listeners;

use RestCord\DiscordClient;
use App\Events\RaidEnded;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class DeleteRaidChannels
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
     * @param  RaidEnded  $event
     * @return void
     */
    public function handle(RaidEnded $event)
    {
        if( !empty( $event->raid->channels ) ) {
            foreach( $event->raid->channels as $channel ) {
                $discord = new DiscordClient(['token' => config('discord.token')]);
                $discord->channel->deleteOrcloseChannel(['channel.id' => (int) $channel->channel_discord_id]);
            }
        }
    }
}
