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
        switch( get_class($event) ) {
            case 'App\Events\Events\TrainCreated' :
                $this->postTrainMessage($event);
                break;

            case 'App\Events\Events\TrainUpdated' :
                $this->postTrainMessage($event);
                break;

            case 'App\Events\Events\TrainStepChecked' :
                $this->postTrainStepCheckedMessage($event);
                break;
        }
    }

    public function postTrainStepCheckedMessage( $event ) {
        $discord = new DiscordClient(['token' => config('discord.token')]);
        if( empty($event->event->channel_discord_id) ) return false;
        if( !$event->guild->settings->events_create_channels ) return false;
        if( !$event->guild->settings->events_trains_add_messages ) return false;
        if( $event->train_step->isLast() ) return false;

        $content = $event->train_step->getDiscordMessage();
        $message = $discord->channel->createMessage(array(
            'channel.id' => intval($event->event->channel_discord_id),
            'content' => $content,
        ));
        $event->train_step->update(['message_discord_id' => $message['id']]);
    }

    public function postTrainMessage( $event ) {
        $discord = new DiscordClient(['token' => config('discord.token')]);
        if( !$event->guild->settings->events_create_channels ) return false;
        if( empty($event->event->channel_discord_id) ) return false;

        $content = $event->train->getDiscordMessage();

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
