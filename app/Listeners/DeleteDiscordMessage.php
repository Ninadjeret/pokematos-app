<?php

namespace App\Listeners;

use App\Models\Guild;
use App\Events\Event;
use App\Events\RaidMessage;
use App\Events\RaidCreated;
use RestCord\DiscordClient;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class DeleteDiscordMessage
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
     * @param  RaidUpdated  $event
     * @return void
     */
    public function handle($event)
    {
        if( empty($event->announce->message_discord_id) ) {
            return ;
        }

        $guild = Guild::find( $event->announce->guild_id );
        if( !$guild ) {
            return;
        }

        Log::debug( print_r($guild->settings, true) );

        if( $event->announce->source == 'text' && $guild->settings->raidreporting_text_delete == false ) return;
        if( $event->announce->source == 'image' && $guild->settings->raidreporting_image_delete == false ) return;

        $discord = new DiscordClient(['token' => config('discord.token')]);
        $discord->channel->deleteMessage([
            'channel.id' => (int) $event->announce->channel_discord_id,
            'message.id' => (int) $event->announce->message_discord_id,
        ]);
    }



}
