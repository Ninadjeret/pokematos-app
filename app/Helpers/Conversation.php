<?php

namespace App\Helpers;

use App\Models\Role;
use RestCord\DiscordClient;
use Illuminate\Support\Facades\Log;

class Conversation {

    public static function getMessage( $type, $soustype, $args = null ) {
        $path = resource_path("conversations/{$type}.json");
        $conversations = json_decode(file_get_contents($path), true);

        $mathing_conversations = array_filter($conversations, function($line) use ($soustype) {
            if( empty($soustype) ) return true;
            if( is_array($soustype) ) {
                return in_array($line['type'], $soustype);
            } else {
                return $soustype == $line['type'];
            }
        });

        if( empty( $mathing_conversations ) ) {
            return false;
        }

        $key = array_rand($mathing_conversations);
        $mathing_conversation = $mathing_conversations[$key];
        if( !empty($args) ) {
            $mathing_conversation['text'] = str_replace(array_keys($args), array_values($args), $mathing_conversation['text']);
            if( !empty($mathing_conversation['next']) ) {
                foreach( $mathing_conversation['next'] as &$message ) {
                    $message = str_replace(array_keys($args), array_values($args), $message);
                }
            }
        }

        return $mathing_conversation;

    }

    public static function sendToDiscord( $channel_id, $guild, $type, $soustype, $args = null ) {
        $message = self::getMessage( $type, $soustype, $args );

        $content = \App\Helpers\Discord::encode($message['text'], $guild, false);
        $discord = new \RestCord\DiscordClient(['token' => config('discord.token')]);
        $discord->channel->createMessage(array(
            'channel.id' => intval($channel_id),
            'content' => $content,
        ));

        if( array_key_exists('next', $message) && !empty($message['next']) ) {
            foreach( $message['next'] as $content ) {
                $content = \App\Helpers\Discord::encode($content, $guild, false);
                usleep( strlen($content) * 75000 );
                $discord->channel->createMessage(array(
                    'channel.id' => intval($channel_id),
                    'content' => $content,
                ));
            }
        }
    }

}
