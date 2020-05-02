<?php

namespace App\Listeners\Discord;

use RestCord\DiscordClient;
use App\Events\Events\TrainStepUnchecked;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class DeleteMessage
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
     * @param  TrainStepUnchecked  $event
     * @return void
     */
    public function handle(TrainStepUnchecked $event)
    {
        $discord = new DiscordClient(['token' => config('discord.token')]);

        switch( get_class($event) ) {

            case 'App\Events\Events\TrainStepUnchecked' :
                if( !empty($event->train_step->message_discord_id) && !empty($event->event->channel_discord_id) ) {
                    \App\Core\Discord::deleteMessage([
                        'channel.id' => intval($event->event->channel_discord_id),
                        'message.id' => intval($event->train_step->message_discord_id),
                    ]);
                }
                break;

            case 'App\Events\Events\TrainStepUnchecked' :
                if( !empty($event->train_step->message_discord_id) && !empty($event->event->channel_discord_id) ) {
                    \App\Core\Discord::deleteMessage([
                        'channel.id' => intval($event->event->channel_discord_id),
                        'message.id' => intval($event->train_step->message_discord_id),
                    ]);
                }
                break;
        }
    }
}
