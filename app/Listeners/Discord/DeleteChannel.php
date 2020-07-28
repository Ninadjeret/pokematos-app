<?php

namespace App\Listeners\Discord;

use RestCord\DiscordClient;
use App\Events\Events\EventDeleted;
use Illuminate\Support\Facades\Log;
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
        $discord = new DiscordClient([
            'token' => config('discord.token')
        ]);

        switch (get_class($event)) {

            case 'App\Events\Events\InvitCanceled':
                if (!empty($event->event->channel_discord_id)) {
                    \App\Core\Discord::deleteChannel([
                        'channel.id' => (int) $event->event->channel_discord_id
                    ]);
                }
                break;

            case 'App\Events\Events\InvitRefused':
                if (!empty($event->event->channel_discord_id)) {
                    \App\Core\Discord::deleteChannel([
                        'channel.id' => (int) $event->event->channel_discord_id
                    ]);
                }
                break;

            case 'App\Events\Events\EventEnded':
                if (!empty($event->event->channel_discord_id) && $event->event->channel_discord_type == 'temp') {
                    \App\Core\Discord::deleteChannel([
                        'channel.id' => (int) $event->event->channel_discord_id
                    ]);
                }
                break;

            case 'App\Events\Events\EventDeleted':
                if (!empty($event->event->channel_discord_id) && $event->event->channel_discord_type == 'temp') {
                    \App\Core\Discord::deleteChannel([
                        'channel.id' => (int) $event->event->channel_discord_id
                    ]);
                }
                break;
        }
    }
}