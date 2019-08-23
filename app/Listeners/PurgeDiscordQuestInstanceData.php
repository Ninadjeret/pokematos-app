<?php

namespace App\Listeners;

use GuzzleHttp\Client;
use RestCord\DiscordClient;
use App\Models\QuestMessage;
use Illuminate\Support\Facades\Log;
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
    public function handle($event)
    {

        $discord = new DiscordClient(['token' => config('discord.token')]);
        $force_delete = false;
        if( $event instanceof \App\Events\QuestInstanceDeleted ) {
            $force_delete = true;
        }

        if( !empty( $event->quest->messages ) ) {
            foreach( $event->quest->messages as $message ) {
                if( !$message->delete_after_end && !$force_delete ) continue;
                $discord->channel->deleteMessage([
                    'channel.id' => (int) $message->channel_discord_id,
                    'message.id' => (int) $message->message_discord_id
                ]);
                QuestMessage::destroy($message->id);
            }
        }

        elseif( $event instanceof \App\Events\DayChanged ) {

            $date = new \DateTime();
            $date->modify('- 1 day');

            $messagesToDelete = QuestMessage::whereNotNull('message_discord_id')
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
                    $message = QuestMessage::where('message_discord_id', $message_ids[0])->first();
                    QuestMessage::destroy($message->id);
                }

                else {

                    Log::debug( print_r($message_ids, true) );

                    $client = new Client();
                    $res = $client->post("https://discordapp.com/api/v6/channels/{$channel_id}/messages/bulk-delete?messages=".json_encode($message_ids), [
                        'headers' => [
                            'Authorization' => 'Bot '.config('discord.token'),
                            'Content-Type' => 'application/json',
                        ],
                        'body' => json_encode(['messages' => $message_ids]),
                    ]);
                    foreach( $message_ids as $message_id ) {
                        $message = QuestMessage::where('message_discord_id', $message_id)->first();
                        QuestMessage::destroy($message->id);
                    }
                }
            }

        }
    }
}
