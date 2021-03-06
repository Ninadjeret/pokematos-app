<?php

namespace App\Listeners\Discord;

use RestCord\DiscordClient;
use App\Events\Events\EventCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class CreateChannel
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
     * @param  EventCreated  $event
     * @return void
     */
    public function handle($event)
    {
        $discord = new DiscordClient(['token' => config('discord.token')]);

        switch( get_class($event) ) {

            case 'App\Events\Events\EventCreated' :
                if( !empty($event->event->channel_discord_id) ) return;
                if( $event->guild->settings->events_create_channels && !empty($event->guild->settings->events_channel_discord_id) ) {
                    $channel = $discord->guild->createGuildChannel([
                        'guild.id' => (int) $event->guild->discord_id,
                        'name' => $event->event->name,
                        'type' => 0,
                        'parent_id' => (int) $event->guild->settings->events_channel_discord_id
                    ]);
                    $event->event->update(['channel_discord_id' => $channel->id]);
                }
                break;

            case 'App\Events\Events\InvitAccepted' :
                if( !empty($event->event->channel_discord_id) ) return;
                if( $event->guild->settings->events_create_channels && !empty($event->guild->settings->events_channel_discord_id) ) {
                    $channel = $discord->guild->createGuildChannel([
                        'guild.id' => (int) $event->guild->discord_id,
                        'name' => $event->event->event->name,
                        'type' => 0,
                        'parent_id' => (int) $event->guild->settings->events_channel_discord_id
                    ]);
                    $event->event->update(['channel_discord_id' => $channel->id]);
                }
                break;
        }
    }
}
