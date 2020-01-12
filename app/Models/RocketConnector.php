<?php

namespace App\Models;

use App\Models\Guild;
use App\Helpers\Helpers;
use RestCord\DiscordClient;
use App\Models\RocketMessage;
use Illuminate\Database\Eloquent\Model;

class RocketConnector extends Model
{
    protected $fillable = [
        'guild_id',
        'name',
        'channel_discord_id',
        'filter_boss_type',
        'filter_boss_bosses',
        'filter_stop_type',
        'filter_stop_zone',
        'filter_stop_stop',
        'format',
        'custom_message',
        'delete_after_end',
    ];
    protected $casts = [
        'filter_boss_bosses' => 'array',
        'filter_stop_zone' => 'array',
        'filter_stop_stop' => 'array',
    ];

    public $roles, $emojis, $channels;

    public function postMessage( $invasion, $announce ) {
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
            $embed = $this->getEmbedMessage($invasion, $announce);
        } elseif( $this->format == 'custom' ) {
            $content = $this->getCustomMessage( $invasion, $announce );
            $embed = [];
        } elseif( $this->format == 'both' ) {
            $content = $this->getCustomMessage( $invasion, $announce );
            $embed = $this->getEmbedMessage($invasion, $announce);
        }

        //On poste le message sur Discord et on log
        try {
            $message = $discord->channel->createMessage(array(
                'channel.id' => intval($this->channel_discord_id),
                'content' => $content,
                'embed' => $embed,
            ));
            RocketMessage::create([
                'invasion_id' => $invasion->id,
                'guild_id' => $this->guild_id,
                'message_discord_id' => $message['id'],
                'channel_discord_id' => $message['channel_id'],
                'delete_after_end' => $this->delete_after_end,
            ]);
        } catch (Exception $e) {
            return false;
        }

    }

    public function getCustomMessage( $invasion, $announce ) {
        return $this->translate( $this->custom_message, $invasion );
    }

    public function translate( $message, $invasion ) {
        $username = ( $invasion->getLastUserAction()->getUser() ) ? $invasion->getLastUserAction()->getUser()->name : false;

        //Gestion des tags
        $patterns = array(
            'rocketboss_name' => html_entity_decode( $invasion->boss->name ),
            'rocketboss_pokemon_1' => ( !empty($invasion->pokemon_step1) ) ? $invasion->pokemon_step1->name : '',
            'rocketboss_pokemon_2' => ( !empty($invasion->pokemon_step2) ) ? $invasion->pokemon_step2->name : '',
            'rocketboss_pokemon_3' => ( !empty($invasion->pokemon_step3) ) ? $invasion->pokemon_step3->name : '',

            'pokestop_nom' => $invasion->getStop()->niantic_name,
            'pokestop_nom_nettoye' => Helpers::sanitize($invasion->getStop()->niantic_name),
            'pokestop_nom_custom' => $invasion->getStop()->name,
            'pokestop_nom_custom_nettoye' => Helpers::sanitize($invasion->getStop()->name),
            'pokestop_description' => $invasion->getStop()->description,
            'pokestop_zone' => ( !empty(  $invasion->getStop()->zone ) ) ?  $invasion->getStop()->zone->name : false,
            'pokestop_zone_nettoye' => ( !empty(  $invasion->getStop()->zone ) ) ?  Helpers::sanitize($invasion->getStop()->zone->name) : false,
            'pokestop_gmaps' => ( !empty(  $invasion->getStop()->google_maps_url ) ) ?  $invasion->getStop()->google_maps_url : false,

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

    public function getEmbedMessage( $invasion, $announce ) {

        $stop = $invasion->getStop();
        $title = "Invasion de {$invasion->boss->name} au Pokéstop {$stop->name}";
        $img_url = $invasion->boss->thumbnail;

        $description = ( !empty($invasion->pokemon_step1) ) ? ":one: {$invasion->pokemon_step1->name}" : ":one: ?" ;
        $description .= ( !empty($invasion->pokemon_step2) ) ? ":two: {$invasion->pokemon_step2->name}" : ":two: ?" ;
        $description .= ( !empty($invasion->pokemon_step3) ) ? ":three: {$invasion->pokemon_step3->name}" : ":three: ?" ;

        //On formatte le embed
        $data = array(
            'title' => $title,
            'description' => $description,
            'color' => hexdec('d4271b'),
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
