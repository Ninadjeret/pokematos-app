<?php

namespace App\Listeners;

use App\Events\RaidDeleted;
use RestCord\DiscordClient;
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
    public function handle(RaidDeleted $event)
    {
        if( !empty( $event->raid->channels ) ) {
            foreach( $event->raid->channels as $channel ) {
                $discord = new DiscordClient(['token' => config('discord.token')]);
                $discord->channel->deleteOrcloseChannel(['channel.id' => (int) $channel->channel_discord_id]);
            }
        }

        if( !empty( $event->raid->messages ) ) {
            foreach( $event->raid->messages as $message ) {
                $discord = new DiscordClient(['token' => config('discord.token')]);
                $discord->channel->deleteMessage([
                    'channel.id' => (int) $message->channel_discord_id,
                    'message.id' => (int) $message->message_discord_id
                ]);
            }
        }
    }
}
