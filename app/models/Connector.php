<?php

namespace App\models;

use RestCord\DiscordClient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Connector extends Model {
    protected $fillable = [
        'guild_id',
        'name',
        'channel_discord_id',
        'publish',
        'filter_gym_type',
        'filter_pokemon_type',
        'filter_gym_zone',
        'filter_gym_gym',
        'filter_pokemon_level',
        'filter_pokemon_pokemon',
        'format',
        'custom_message_before',
        'custom_message_after',
    ];
    protected $casts = [
        'filter_gym_zone' => 'array',
        'filter_gym_gym' => 'array',
        'filter_pokemon_level' => 'array',
        'filter_pokemon_pokemon' => 'array',
    ];

    public function postMessage( $raid, $announce ) {
        if( empty( $this->channel_discord_id ) ) return false;

        if( $this->format == 'auto' ) {
            $this->postEmbedMessage( $raid, $announce );
        } else {
            $this->postCustomMessage( $raid, $announce );
        }

    }

    public function postEmbedMessage( $raid, $announce ) {
        $raid_embed = $this->getEmbedMessage($raid, $announce);
        $discord = new DiscordClient(['token' => config('discord.token')]);
        try {
            $message = $discord->channel->createMessage(array(
                'channel.id' => intval($this->channel_discord_id),
                'content' => '',
                'embed' => $raid_embed,
            ));
            RaidMessage::create([
                'raid_id' => $raid->id,
                'guild_id' => $this->guild_id,
                'message_discord_id' => $message['id'],
                'channel_discord_id' => $message['channel_id'],
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function postCustomMessage( $raid, $announce ) {
        $discord = new DiscordClient(['token' => config('discord.token')]);
        $content = $this->getCustomMessage( $raid, $announce );
        try {
            $message = $discord->channel->createMessage(array(
                'channel.id' => intval($this->channel_discord_id),
                'content' => $content,
            ));
            RaidMessage::create([
                'raid_id' => $raid->id,
                'guild_id' => $this->guild_id,
                'message_discord_id' => $message['id'],
                'channel_discord_id' => $message['channel_id'],
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function getCustomMessage( $raid, $announce ) {

        $discord = new DiscordClient(['token' => config('discord.token')]);
        $guild = Guild::find( $this->guild_id );

        if( $raid->isFuture() ) {
            $message = $this->custom_message_before;
        } else {
            $message = $this->custom_message_after;
        }

        //Gestion des tags
        $patterns = array(
            'raid_pokemon' => ( !$raid->pokemon ) ? false : html_entity_decode( $raid->pokemon->name_fr ),
            'raid_niveau' => $raid->egg_level,
            'raid_debut' => $raid->getStartTime()->format('H\hi'),
            'raid_fin' => $raid->getEndTime()->format('H\hi'),

            'arene_nom' => $raid->getGym()->niantic_name,
            //'nom_officiel_de_l_arene_nettoye' => sanitize_title($raid->getGym()->nianticId),
            'arene_nom_custom' => $raid->getGym()->name,
            //'nom_custom_de_l_arene_nettoye' => sanitize_title($raid->getGym()->getNameFr()),
            'arene_description' => $raid->getGym()->description,
            'arene_zone' => ( !empty(  $raid->getGym()->zone ) ) ?  $raid->getGym()->zone->name : false,
            'arene_gmaps' => ( !empty(  $raid->getGym()->google_maps_url ) ) ?  $raid->getGym()->google_maps_url : false,

            'utilisateur' => ( $raid->getLastAnnounce()->getUser() ) ? $raid->getLastAnnounce()->getUser()->name : false,
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

    public function getEmbedMessage( $raid, $announce ) {

        //Gestion des infos du raid
        $description = '';
        $title = 'Raid '.$raid->egg_level.' têtes';
        $img_url = "https://assets.profchen.fr/img/eggs/egg_".$raid->egg_level.".png";

        $startTime = new \DateTime($raid->start_time);
        $endTime = new \DateTime($raid->end_time);

        if( $raid->start_time) {
            $title .= ' à '.$startTime->format('H\hi');
            $description = "Pop : de ".$startTime->format('H\hi')." à ".$endTime->format('H\hi');
        }

        if( $raid->pokemon ) {
            $title = html_entity_decode('Raid '.$raid->pokemon->name_fr.' jusqu\'à '.$endTime->format('H\hi'));
            $img_url = $raid->pokemon->thumbnail_url;
        }

        $gymName = html_entity_decode( $raid->getGym()->name );
        if( $raid->getGym()->zone_id ) {
            $gymName = $raid->getGym()->zone->name.' - '.$gymName;
        }

        //Gestion EX
        if( $raid->egg_level == 6 && empty( $raid->pokemon ) ) {
            $title = 'Raid EX le '.$startTime->format('d/m').' à '.$startTime->format('H\hi');
            if( $raid->channels ) {
                foreach( $raid->channels as $channel ) {
                    if( $channel->guild_id == $this->guild_id ) {
                        $description = 'Vous pouvez vous organiser dans le salon <#'.$channel->channel_discord_id.'>';
                    }
                }
            }

        }

        //On formatte le embed
        $data = array(
            'title' => $title,
            'description' => $description,
            'color' => $this->getEggColor( $raid->egg_level ),
            'thumbnail' => array(
                'url' => $img_url
            ),
            'author' => array(
                'name' => $gymName,
                'url' => $raid->getGym()->google_maps_url,
                'icon_url' => 'https://d30y9cdsu7xlg0.cloudfront.net/png/4096-200.png'
            ),
        );

        return $data;
    }

    public function getEggColor( $eggLevel ) {
        $colors = array(
            1 => 'de6591',
            2 => 'de6591',
            3 => 'efad02',
            4 => 'efad02',
            5 => '222',
        );

        if(array_key_exists($eggLevel, $colors) ) {
            return hexdec( $colors[$eggLevel] );
        }
        return false;
    }

}
