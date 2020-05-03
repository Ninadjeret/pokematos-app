<?php

namespace App\Listeners;

use GuzzleHttp\Client;
use RestCord\DiscordClient;
use App\Models\RocketMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class PurgeDiscordInvasionData
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
     * @param  DayChanged  $event
     * @return void
     */
    public function handle($event)
    {
        $discord = new DiscordClient(['token' => config('discord.token')]);
        $force_delete = false;
        if( $event instanceof \App\Events\RocketInvasionDeleted || $event instanceof \App\Events\RocketInvasionUpdated ) {
            $force_delete = true;
        }

        if( !empty( $event->invasion->messages ) ) {
            foreach( $event->invasion->messages as $message ) {
                if( !$message->delete_after_end && !$force_delete ) continue;
                $discord->channel->deleteMessage([
                    'channel.id' => (int) $message->channel_discord_id,
                    'message.id' => (int) $message->message_discord_id
                ]);
                RocketMessage::destroy($message->id);
            }
        }

        elseif( $event instanceof \App\Events\DayChanged ) {
            $date = new \DateTime();
            $date->modify('- 1 day');

            $messagesToDelete = RocketMessage::whereNotNull('message_discord_id')
                ->where('delete_after_end', 1)
                ->whereDate('created_at', '<=', $date->format('Y-m-d'))
                ->get();
            //Log::debug( print_r($messagesToDelete->toArray(), true) );

            if( empty($messagesToDelete) ) {
                return;
            }

            //On regroupe par salon pour faire les suppressions
            $deletePerChannel = [];
            foreach( $messagesToDelete as $message ) {
                if( !array_key_exists($message->channel_discord_id, $deletePerChannel) ) {
                    $deletePerChannel[$message->channel_discord_id] = [];
                }
                $deletePerChannel[$message->channel_discord_id][] = $message->message_discord_id;
            }

            //Pour chaque salon, on regarde si on peut bulk-delete ou pas
            foreach( $deletePerChannel as $channel_id => $message_ids ) {
                sleep(1);
                if( count($message_ids) === 1 ) {
                    $discord->channel->deleteMessage([
                        'channel.id' => (int) $channel_id,
                        'message.id' => (int) $message_ids[0]
                    ]);
                    $message = RocketMessage::where('message_discord_id', $message_ids[0])->first();
                    RocketMessage::destroy($message->id);
                }

                else {
                    \App\Core\Discord::bulkDeleteMessages([
                        'channel_id' => $channel_id,
                        'messages_ids' => $message_ids,
                    ]);
                    foreach( $message_ids as $message_id ) {
                        $message = RocketMessage::where('message_discord_id', $message_id)->first();
                        RocketMessage::destroy($message->id);
                    }
                }
            }
        }
    }
}
