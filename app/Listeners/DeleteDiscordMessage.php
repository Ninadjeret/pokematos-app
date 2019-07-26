<?php

namespace App\Listeners;

use App\Events\RaidUpdated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

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
    public function handle(Event $event)
    {
        if( $event->announce->type == 'map' ) {
            return ;
        }

        $guild = Guild::find( $event->announce->guild_id );
        if( !$guild ) {
            return;
        }

        if( $event->announce->type == 'text' && $guild->settings->raidreporting_text_delete != true ) return;
        if( $event->announce->type == 'image' && $guild->settings->raidreporting_image_delete != true ) return;

        $discord = new DiscordClient(['token' => config('discord.token')]);
        $discord->channel->deleteMessage([
            'channel.id' => (int) $event->announce->channel_discord_id,
            'message.id' => (int) $event->announce->message_discord_id,
        ]);
    }
}
