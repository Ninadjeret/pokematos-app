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

    public $roles, $emojis, $channels;

    public function postMessage( $quest, $announce ) {
        if( empty( $this->channel_discord_id ) ) return false;
        $guild = Guild::find( $this->guild_id );

        //On initialise les infos discord
        $discord = new DiscordClient(['token' => config('discord.token')]);
        $this->roles = $discord->guild->getGuildRoles(array(
            'guild.id' => intval($guild->discord_id)
        ));
        $this->channels = $discord->guild->getGuildChannels(array(
            'guild.id' => intval($guild->discord_id)
        ));
        $this->emojis = $discord->emoji->listGuildEmojis(array(
            'guild.id' => intval($guild->discord_id)
        ));

        //Récupération du message selon le format choisi
        if( $this->format == 'auto' ) {
            $content = '';
            $embed = $this->getEmbedMessage($quest, $announce);
        } elseif( $this->format == 'custom' ) {
            $content = $this->getCustomMessage( $quest, $announce );
            $embed = [];
        } elseif( $this->format == 'both' ) {
            $content = $this->getCustomMessage( $quest, $announce );
            $embed = $this->getEmbedMessage($quest, $announce);
        }

        //On poste le message sur Discord et on log
        try {
            $message = $discord->channel->createMessage(array(
                'channel.id' => intval($this->channel_discord_id),
                'content' => $content,
                'embed' => $embed,
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
        return $this->translate( $this->custom_message, $quest );
    }

    public function translate( $message, $quest ) {
        $username = ( $quest->getLastUserAction()->getUser() ) ? $quest->getLastUserAction()->getUser()->name : false;

        //Gestion des tags
        $patterns = array(
            'quete_recompense' => ( !$quest->reward ) ? false : html_entity_decode( $quest->reward->name ),
            'quete_nom' => $quest->name,

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
            foreach( $this->roles as $role ) {
                if( strstr( $message, '@'.$role->name ) ) {
                    $message = str_replace('@'.$role->name, '<@&'.$role->id.'>', $message);
                }
            }
        }

        if( $username && strstr( $message, '@'.$username ) ) {
            $user = $quest->getLastUserAction()->getUser();
            $message = str_replace('@'.$username, '<@!'.$user->discord_id.'>', $message);
        }

        //Gestion des salons #
        if( strstr( $message, '#' ) ) {
            foreach( $this->channels as $channel ) {
                if( strstr( $message, '#'.$channel->name ) ) {
                    $message = str_replace('#'.$channel->name, '<#'.$channel->id.'>', $message);
                }
            }
        }

        //Gestion des emojis
        if( strstr( $message, ':' ) ) {
            if( !empty($this->emojis) ) {
                foreach( $this->emojis as $emoji ) {
                    if( strstr( $message, ':'.$emoji->name.':' ) ) {
                        $message = str_replace(':'.$emoji->name.':', '<:'.$emoji->name.':'.$emoji->id.'>', $message);
                    }
                }
            }
        }

        //On nettoye les arobases inutles (sans fare de regex parce que c'est chiant ^^)
        $message = str_replace('<@', '##<##', $message);
        $message = str_replace('@', '', $message);
        $message = str_replace('##<##', '<@', $message);

        return $message;
    }

    public function getEmbedMessage( $quest, $announce ) {


        $description = '';
        if( !empty($quest->reward_type) ) {
            $title = "Quête {$quest->reward->name} en cours";
            $img_url = $quest->reward->thumbnail_url;
            if( !empty($quest->name) ) {
                $description = $quest->name;
            }
        } else {
            $title = "Quête {$quest->name} en cours";
            $img_url = 'https://assets.profchen.fr/img/app/unknown.png';
            $description = 'On ne connait pas encore la récompense';
        }

        //On formatte le embed
        $data = array(
            'title' => $title,
            'description' => $description,
            'color' => hexdec('5bb0c4'),
            'thumbnail' => array(
                'url' => $img_url
            ),
            'author' => array(
                'name' => $quest->getStop()->name,
                'url' => $quest->getStop()->google_maps_url,
                'icon_url' => 'https://assets.profchen.fr/img/app/connector_pokestop.png'
            ),
        );

        return $data;
    }

}
