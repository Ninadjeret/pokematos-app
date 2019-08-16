<?php

namespace App\Models;

use RestCord\DiscordClient;
use App\Models\QuestMessage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class QuestConnector extends Model
{
    protected $fillable = [
        'guild_id',
        'name',
        'channel_discord_id',
        'filter_reward_type',
        'filter_reward_reward',
        'filter_reward_pokemon',
        'filter_stop_type',
        'filter_stop_zone',
        'filter_stop_stop',
        'format',
        'custom_message',
        'delete_after_end',
    ];
    protected $casts = [
        'filter_reward_reward' => 'array',
        'filter_reward_pokemon' => 'array',
        'filter_stop_zone' => 'array',
        'filter_stop_stop' => 'array',
    ];

    public function postMessage( $quest, $announce ) {
        if( empty( $this->channel_discord_id ) ) return false;

        if( $this->format == 'auto' ) {
            $this->postEmbedMessage( $quest, $announce );
        } else {
            $this->postCustomMessage( $quest, $announce );
        }

    }

    public function postEmbedMessage( $quest, $announce ) {
        $quest_embed = $this->getEmbedMessage($quest, $announce);
        $discord = new DiscordClient(['token' => config('discord.token')]);
        try {
            $message = $discord->channel->createMessage(array(
                'channel.id' => intval($this->channel_discord_id),
                'content' => '',
                'embed' => $quest_embed,
            ));
            QuestMessage::create([
                'quest_instance_id' => $quest->id,
                'guild_id' => $this->guild_id,
                'message_discord_id' => $message['id'],
                'channel_discord_id' => $message['channel_id'],
                'delete_after_end' => $this->delete_after_end,
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function postCustomMessage( $quest, $announce ) {
        $discord = new DiscordClient(['token' => config('discord.token')]);
        $content = $this->getCustomMessage( $quest, $announce );
        try {
            $message = $discord->channel->createMessage(array(
                'channel.id' => intval($this->channel_discord_id),
                'content' => $content,
            ));
            QuestMessage::create([
                'quest_instance_id' => $quest->id,
                'guild_id' => $this->guild_id,
                'message_discord_id' => $message['id'],
                'channel_discord_id' => $message['channel_id'],
                'delete_after_end' => $this->delete_after_end,
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function getCustomMessage( $quest, $announce ) {

        $discord = new DiscordClient(['token' => config('discord.token')]);
        $guild = Guild::find( $this->guild_id );
        $message = $this->custom_message;
        $username = ( $quest->getLastAnnounce()->getUser() ) ? $quest->getLastAnnounce()->getUser()->name : false;

        //Gestion des tags
        $patterns = array(
            'quete_recompense' => ( !$quest->quest->pokemon ) ? false : html_entity_decode( $quest->quest->pokemon->name_fr ),
            'quete_nom' => $quest->quest->name,

            'pokestop_nom' => $quest->getStop()->niantic_name,
            'pokestop_nom_custom' => $quest->getStop()->name,
            'pokestop_description' => $quest->getStop()->description,
            'pokestop_zone' => ( !empty(  $quest->getStop()->zone ) ) ?  $quest->getStop()->zone->name : false,
            'pokestop_gmaps' => ( !empty(  $quest->getStop()->google_maps_url ) ) ?  $quest->getStop()->google_maps_url : false,

            'utilisateur' => $username,
        );
        foreach( $patterns as $pattern => $valeur ) {
            $message = str_replace( '{'.$pattern.'}', $valeur, $message );
        }

        //Gestion des mentions
        if( strstr( $message, '@' ) ) {
            $roles = $discord->guild->getGuildRoles(array(
                'guild.id' => intval($guild->discord_id)
            ));
            foreach( $roles as $role ) {
                if( strstr( $message, '@'.$role->name ) ) {
                    $message = str_replace('@'.$role->name, '<@&'.$role->id.'>', $message);
                }
            }
        }

        if( $username && strstr( $message, '@'.$username ) ) {
            $user = $quest->getLastAnnounce()->getUser();
            $message = str_replace('@'.$username, '<@!'.$user->discord_id.'>', $message);
        }

        //Gestion des salons #
        if( strstr( $message, '#' ) ) {
            $channels = $discord->guild->getGuildChannels(array(
                'guild.id' => intval($guild->discord_id)
            ));
            foreach( $channels as $channel ) {
                if( strstr( $message, '#'.$channel->name ) ) {
                    $message = str_replace('#'.$channel->name, '<#'.$channel->id.'>', $message);
                }
            }
        }

        //Gestion des emojis
        if( strstr( $message, ':' ) ) {
            $emojis = $discord->emoji->listGuildEmojis(array(
                'guild.id' => intval($guild->discord_id)
            ));
            if( !empty($emojis) ) {
                foreach( $emojis as $emoji ) {
                    if( strstr( $message, ':'.$emoji->name.':' ) ) {
                        $message = str_replace(':'.$emoji->name.':', '<:'.$emoji->name.':'.$emoji->id.'>', $message);
                    }
                }
            }
        }

        return $message;
    }

    public function getEmbedMessage( $quest, $announce ) {

        //Gestion des infos du raid
        $img_url = ( isset( $quest->quest->pokemon ) && !empty( $quest->quest->pokemon ) ) ? $quest->quest->pokemon->thumbnail_url : false ;

        //On formatte le embed
        $data = array(
            'title' => 'Quête '.$quest->quest->name. ' au Pokéstop '.$quest->getStop()->name,
            'description' => '',
            'color' => hexdec('5bb0c4'),
            'thumbnail' => array(
                'url' => $img_url
            ),
            'author' => array(
                'name' => $quest->getStop()->name,
                'url' => $quest->getStop()->google_maps_url,
                'icon_url' => 'https://d30y9cdsu7xlg0.cloudfront.net/png/4096-200.png'
            ),
        );

        return $data;
    }

}
