<?php

namespace App\Models;

use App\Models\Stop;
use App\Models\Zone;
use App\Models\Guild;
use App\Core\Helpers;
use App\Models\RocketBoss;
use RestCord\DiscordClient;
use App\Models\RocketMessage;
use Illuminate\Support\Facades\Log;
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
        'filtered_bosses' => 'array',
        'filtered_zones' => 'array',
        'filtered_stops' => 'array',
    ];
    protected $appends = ['filtered_bosses', 'filtered_zones', 'filtered_stops'];

    public $roles, $emojis, $channels;

    public function getFilteredBossesAttribute()
    {
        if (empty($this->filter_boss_bosses)) {
            return [];
        }
        $bosses = [];
        foreach ($this->filter_boss_bosses as $boss_id) {
            $boss = RocketBoss::find($boss_id);
            if ($boss) {
                $bosses[] = $boss;
            }
        }
        return $bosses;
    }

    public function getFilteredZonesAttribute()
    {
        if (empty($this->filter_stop_zone)) {
            return [];
        }
        $zones = [];
        foreach ($this->filter_stop_zone as $zone_id) {
            $zone = Zone::find($zone_id);
            if ($zone) {
                $zones[] = $zone;
            }
        }
        return $zones;
    }

    public function getFilteredStopsAttribute()
    {
        if (empty($this->filter_stop_stop)) {
            return [];
        }
        $stops = [];
        foreach ($this->filter_stop_stop as $stop_id) {
            $stop = Stop::find($stop_id);
            if ($stop) {
                $stops[] = $stop;
            }
        }
        return $stops;
    }

    public function postMessage($invasion, $announce)
    {
        if (empty($this->channel_discord_id)) return false;
        $guild = Guild::find($this->guild_id);

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
        if ($this->format == 'auto') {
            $content = '';
            $embed = $this->getEmbedMessage($invasion, $announce, $guild);
        } elseif ($this->format == 'custom') {
            $content = $this->getCustomMessage($invasion, $announce, $guild);
            $embed = [];
        } elseif ($this->format == 'both') {
            $content = $this->getCustomMessage($invasion, $announce, $guild);
            $embed = $this->getEmbedMessage($invasion, $announce, $guild);
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

    public function getCustomMessage($invasion, $announce, $guild)
    {
        return $this->translate($this->custom_message, $invasion, $guild);
    }

    public function translate($message, $invasion, $guild)
    {
        $username = ($invasion->getLastUserAction()->getUser()) ? $invasion->getLastUserAction()->getUser()->getNickname($guild->id) : false;

        $role_poi_lie = Role::where('gym_id', $invasion->getStop()->id)->first();
        $role_zone_liee = ($invasion->getStop()->zone) ? Role::where('zone_id', $invasion->getStop()->zone->id)->first() : false;

        //Gestion des tags
        $patterns = array(
            'rocketboss_name' => html_entity_decode($invasion->boss->name),
            'rocketboss_pokemon_1' => (!empty($invasion->pokemon_step1)) ? $invasion->pokemon_step1->name : '?',
            'rocketboss_pokemon_2' => (!empty($invasion->pokemon_step2)) ? $invasion->pokemon_step2->name : '?',
            'rocketboss_pokemon_3' => (!empty($invasion->pokemon_step3)) ? $invasion->pokemon_step3->name : '?',

            'pokestop_nom' => $invasion->getStop()->niantic_name,
            'pokestop_nom_nettoye' => Helpers::sanitize($invasion->getStop()->niantic_name),
            'pokestop_nom_custom' => $invasion->getStop()->name,
            'pokestop_nom_custom_nettoye' => Helpers::sanitize($invasion->getStop()->name),
            'pokestop_description' => $invasion->getStop()->description,
            'pokestop_zone' => (!empty($invasion->getStop()->zone)) ?  $invasion->getStop()->zone->name : false,
            'pokestop_zone_nettoye' => (!empty($invasion->getStop()->zone)) ?  Helpers::sanitize($invasion->getStop()->zone->name) : false,
            'pokestop_gmaps' => (!empty($invasion->getStop()->google_maps_url)) ?  $invasion->getStop()->google_maps_url : false,

            'role_poi_lie' => (!empty($role_poi_lie)) ? "@{$role_poi_lie->name}" : '',
            'role_zone_liee' => (!empty($role_zone_liee)) ? "@{$role_zone_liee->name}" : '',

            'utilisateur' => $username,
        );
        foreach ($patterns as $pattern => $valeur) {
            $message = str_replace('{' . $pattern . '}', $valeur, $message);
        }

        //Gestion des mentions
        if (strstr($message, '@')) {
            foreach ($this->roles as $role) {
                if (strstr($message, '@' . $role->name)) {
                    $message = str_replace('@' . $role->name, '<@&' . $role->id . '>', $message);
                }
            }
        }

        if ($username && strstr($message, '@' . $username)) {
            $user = $invasion->getLastUserAction()->getUser();
            $message = str_replace('@' . $username, '<@!' . $user->discord_id . '>', $message);
        }

        //Gestion des salons #
        if (strstr($message, '#')) {
            foreach ($this->channels as $channel) {
                if (strstr($message, '#' . $channel->name)) {
                    $message = str_replace('#' . $channel->name, '<#' . $channel->id . '>', $message);
                }
            }
        }

        //Gestion des emojis
        if (strstr($message, ':')) {
            if (!empty($this->emojis)) {
                foreach ($this->emojis as $emoji) {
                    if (strstr($message, ':' . $emoji->name . ':')) {
                        $message = str_replace(':' . $emoji->name . ':', '<:' . $emoji->name . ':' . $emoji->id . '>', $message);
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

    public function getEmbedMessage($invasion, $announce, $guild)
    {
        $stop = $invasion->getStop();
        $title = "Invasion de {$invasion->boss->name} au Pokéstop {$stop->name}";
        $img_url = $invasion->boss->thumbnails->base;

        $description = (!empty($invasion->pokemon_step1)) ? ":one: {$invasion->pokemon_step1->name}\r\n" : ":one: ?\r\n";
        $description .= (!empty($invasion->pokemon_step2)) ? ":two: {$invasion->pokemon_step2->name}\r\n" : ":two: ?\r\n";
        $description .= (!empty($invasion->pokemon_step3)) ? ":three: {$invasion->pokemon_step3->name}" : ":three: ?";

        //On formatte le embed
        $data = array(
            'title' => $title,
            'description' => $description,
            'color' => hexdec('d4271b'),
            'thumbnail' => array(
                'url' => $img_url
            ),
            'author' => array(
                'name' => $invasion->getStop()->name,
                'url' => $invasion->getStop()->google_maps_url,
                'icon_url' => asset('storage/img/static/connector_pokestop_rocket.png')
            ),
        );

        return $data;
    }
}