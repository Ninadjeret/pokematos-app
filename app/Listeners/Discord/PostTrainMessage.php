<?php

namespace App\Listeners\Discord;

use RestCord\DiscordClient;
use App\Events\Events\TrainUpdated;
use App\Events\Events\TrainCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class PostTrainMessage
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
     * @param  TrainUpdated  $event
     * @return void
     */
    public function handle($event)
    {
        $discord = new DiscordClient(['token' => config('discord.token')]);

        Log::debug( print_r('tu', true) );
        if( empty($event->event->channel_discord_id) ) return false;

        $content = $event->train->getDiscordMessage();
        Log::debug( print_r($content, true) );
        Log::debug( print_r($event->train->message_discord_id, true) );
        if( empty($event->train->message_discord_id) ) {
            $message = $discord->channel->createMessage(array(
                'channel.id' => intval($event->event->channel_discord_id),
                'content' => $content,
            ));
            $discord->channel->addPinnedChannelMessage([
                'channel.id' => intval($event->event->channel_discord_id),
                'message.id' => intval($message['id']),
            ]);
            $event->train->update(['message_discord_id' => $message['id']]);
        } else {
            $message = $discord->channel->editMessage(array(
                'channel.id' => intval($event->event->channel_discord_id),
                'message.id' => intval($event->train->message_discord_id),
                'content' => $content,
            ));
        }

    }
}
