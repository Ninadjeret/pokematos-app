<?php

namespace App\Models;

use RestCord\DiscordClient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Connector extends Model {
    protected $fillable = [
        'guild_id',
        'name',
        'channel_discord_id',
        'filter_gym_type',
        'filter_pokemon_type',
        'filter_gym_zone',
        'filter_gym_gym',
        'filter_pokemon_level',
        'filter_pokemon_pokemon',
        'filter_source_type',
        'format',
        'custom_message_before',
        'custom_message_after',
        'auto_settings',
        'delete_after_end',
    ];
    protected $casts = [
        'filter_gym_zone' => 'array',
        'filter_gym_gym' => 'array',
        'filter_pokemon_level' => 'array',
        'filter_pokemon_pokemon' => 'array',
        'filter_source_type' => 'array',
        'auto_settings' => 'array',
    ];

    public $roles, $emojis, $channels;

    public function postMessage( $raid, $announce ) {
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
            $embed = $this->getEmbedMessage($raid, $announce);
        } elseif( $this->format == 'custom' ) {
            $content = $this->getCustomMessage( $raid, $announce );
            $embed = [];
        } elseif( $this->format == 'both' ) {
            $content = $this->getCustomMessage( $raid, $announce );
            $embed = $this->getEmbedMessage($raid, $announce);
        }

        //On poste le message sur Discord et on log
        try {
            $message = $discord->channel->createMessage(array(
                'channel.id' => intval($this->channel_discord_id),
                'content' => $content,
                'embed' => $embed,
            ));
            RaidMessage::create([
                'raid_id' => $raid->id,
                'guild_id' => $this->guild_id,
                'message_discord_id' => $message['id'],
                'channel_discord_id' => $message['channel_id'],
                'delete_after_end' => $this->delete_after_end,
            ]);
        } catch (Exception $e) {
            return false;
        }

    }

    public function getCustomMessage( $raid, $announce ) {
        if( $raid->isFuture() ) {
            $message = $this->custom_message_before;
        } else {
            $message = $this->custom_message_after;
        }
        return $this->translate($message, $raid);
    }

    private function translate( $message, $raid ) {

        $username = ( $raid->getLastAnnounce()->getUser() ) ? $raid->getLastAnnounce()->getUser()->name : false ;

        //Gestion des tags
        $patterns = array(
            'raid_pokemon' => ( !$raid->pokemon ) ? false : html_entity_decode( $raid->pokemon->name_fr ),
            'raid_niveau' => $raid->egg_level,
            'raid_debut' => $raid->getStartTime()->format('H\hi'),
            'raid_fin' => $raid->getEndTime()->format('H\hi'),

            'arene_nom' => $raid->getGym()->niantic_name,
            'arene_nom_custom' => $raid->getGym()->name,
            'arene_description' => $raid->getGym()->description,
            'arene_zone' => ( !empty(  $raid->getGym()->zone ) ) ?  $raid->getGym()->zone->name : false,
            'arene_gmaps' => ( !empty(  $raid->getGym()->google_maps_url ) ) ?  $raid->getGym()->google_maps_url : false,

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
            $user = $raid->getLastAnnounce()->getUser();
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

        return $message;
    }

    public function getEmbedMessage( $raid, $announce ) {

        //Gestion des infos du raid
        $description = [];
        $title = 'Raid '.$raid->egg_level.' têtes';
        $img_url = "https://assets.profchen.fr/img/eggs/egg_".$raid->egg_level.".png";

        $startTime = new \DateTime($raid->start_time);
        $endTime = new \DateTime($raid->end_time);

        if( $raid->start_time) {
            $title .= ' à '.$startTime->format('H\hi');
            $description[] = "Pop : de ".$startTime->format('H\hi')." à ".$endTime->format('H\hi');
        }

        if( $raid->pokemon ) {
            $title = html_entity_decode('Raid '.$raid->pokemon->name_fr.' jusqu\'à '.$endTime->format('H\hi'));
            $img_url = $raid->pokemon->thumbnail_url;
        }

        $gymName = html_entity_decode( $raid->getGym()->name );
        if( $raid->getGym()->zone_id ) {
            $gymName = $raid->getGym()->zone->name.' - '.$gymName;
        }

        if( is_array( $this->auto_settings ) ) {
            if( in_array('cp', $this->auto_settings ) && $raid->pokemon ) {
                $description[] = "Normal : CP entre ".$raid->pokemon->cp['lvl20']['min']." et ".$raid->pokemon->cp['lvl20']['max']."\r\n".
                "Bost Météo : CP entre ".$raid->pokemon->cp['lvl25']['min']." et ".$raid->pokemon->cp['lvl25']['max'];
            }
            if( in_array('arene_desc', $this->auto_settings ) && !empty($raid->getGym()->description) ) {
                $description[] = $this->translate($raid->getGym()->description, $raid);
            }
        }

        //Gestion EX
        if( $raid->egg_level == 6 ) {
            $title = 'Raid EX le '.$startTime->format('d/m').' à '.$startTime->format('H\hi');
            if( $raid->channels ) {
                foreach( $raid->channels as $channel ) {
                    if( $channel->guild_id == $this->guild_id ) {
                        $description[] = 'Vous pouvez vous organiser dans le salon <#'.$channel->channel_discord_id.'>';
                    }
                }
            }

        }

        //On formatte le embed
        $icon_url = ( $raid->getGym()->ex ) ? 'https://assets.profchen.fr/img/map/map_marker_default_ex_03.png' : 'https://assets.profchen.fr/img/map/map_marker_default_01.png' ;
        $data = array(
            'title' => $title,
            'description' => ( !empty($description) ) ? implode("\r\n\r\n", $description) : '',
            'color' => $this->getEggColor( $raid->egg_level ),
            'thumbnail' => array(
                'url' => $img_url
            ),
            'author' => array(
                'name' => $gymName,
                'url' => $raid->getGym()->google_maps_url,
                'icon_url' => $icon_url
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
