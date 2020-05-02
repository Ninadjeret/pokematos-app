<?php

namespace App\Core;

use App\Models\Role;
use GuzzleHttp\Client;
use RestCord\DiscordClient;
use Illuminate\Support\Facades\Log;

class Discord {
    public static function encode( $message, $guild, $user ) {

        $discord = new DiscordClient(['token' => config('discord.token')]);
        $roles = $discord->guild->getGuildRoles(array(
            'guild.id' => intval($guild->discord_id)
        ));
        $channels = $discord->guild->getGuildChannels(array(
            'guild.id' => intval($guild->discord_id)
        ));
        $emojis = $discord->emoji->listGuildEmojis(array(
            'guild.id' => intval($guild->discord_id)
        ));


        //Gestion des mentions
        if( strstr( $message, '@' ) ) {
            foreach( $roles as $role ) {
                if( strstr( $message, '@'.$role->name ) ) {
                    $message = str_replace('@'.$role->name, '<@&'.$role->id.'>', $message);
                }
            }
        }

        if( strstr( $message, '@{utilisateur}' ) ) {
            $message = str_replace('@{utilisateur}', '<@!'.$user->discord_id.'>', $message);
        }

        //Gestion des salons #
        if( strstr( $message, '#' ) ) {
            foreach( $channels as $channel ) {
                if( strstr( $message, '#'.$channel->name ) ) {
                    $message = str_replace('#'.$channel->name, '<#'.$channel->id.'>', $message);
                }
            }
        }

        //Gestion des emojis
        if( strstr( $message, ':' ) ) {
            if( !empty($emojis) ) {
                foreach( $emojis as $emoji ) {
                    if( strstr( $message, ':'.$emoji->name.':' ) ) {
                        $message = str_replace(':'.$emoji->name.':', '<:'.$emoji->name.':'.$emoji->id.'>', $message);
                    }
                }
            }
        }

        //On nettoye les arobases inutles (sans fare de regex parce que c'est chiant ^^)
        $message = str_replace('@here', '{{here}}', $message);
        $message = str_replace('<@', '##<##', $message);
        $message = str_replace('@', '', $message);
        $message = str_replace('##<##', '<@', $message);
        $message = str_replace('{{here}}', '@here', $message);
        return $message;
    }

    public static function translateFrom( $message, $guild, $user = false ) {

        $discord = new DiscordClient(['token' => config('discord.token')]);
        $roles = $discord->guild->getGuildRoles(array(
            'guild.id' => intval($guild->discord_id)
        ));

        //preg_match('/\<\!/i', $message, $out);
        preg_match_all("/<@&([0-9]*)>/", $message, $mentions, PREG_SET_ORDER);

        if( !empty( $mentions ) ) {
            foreach( $mentions as $mention ) {
                $role = Role::where('discord_id', $mention[1])->first();
                if( $role ) {
                    $message = str_replace($mention[0], '@'.$role->name, $message);
                }
            }
        }

        return $message;

    }

    public static function sendMessage($args) {
        $discord = new DiscordClient(['token' => config('discord.token')]);
        $message = $discord->channel->createMessage($args);
        if( isset($message['id']) ){
            return $message;
        }
        return false;
    }

    public static function deleteMessage($args) {
        try {
            $discord = new DiscordClient(['token' => config('discord.token')]);
            $channel = $discord->channel->deleteOrcloseChannel([
                'channel.id' => (int) $event->event->channel_discord_id,
            ]);
            $event->event->update(['channel_discord_id' => null]);
        }
        catch (\GuzzleHttp\Command\Exception\CommandException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
        }
    }

    public static function SyncBot() {
        $client = new Client();
        $url = config('app.bot_sync_url');
        if( !empty($url) ) {
            $res = $client->get($url);
        }
    }
}
