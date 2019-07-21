<?php

namespace App\Listeners;

use RestCord\DiscordClient;
use App\Events\QuestInstanceDeleted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class PurgeDiscordQuestInstanceData
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
     * @param  QuestInstanceDeleted  $event
     * @return void
     */
    public function handle(QuestInstanceDeleted $event)
    {
        if( !empty( $event->quest->messages ) ) {
            foreach( $event->quest->messages as $message ) {
                $discord = new DiscordClient(['token' => config('discord.token')]);
                $discord->channel->deleteMessage([
                    'channel.id' => (int) $message->channel_discord_id,
                    'message.id' => (int) $message->message_discord_id
                ]);
            }
        }
    }
}
