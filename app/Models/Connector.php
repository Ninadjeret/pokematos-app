<?php

namespace App\Models;

use App\Models\Stop;
use App\Models\Zone;
use App\Core\Helpers;
use App\Models\Guild;
use App\Models\Pokemon;
use RestCord\DiscordClient;
use App\Models\DiscordMessage;
use App\Core\Raids\RaidHelpers;
use Illuminate\Support\Facades\Log;
use App\Core\Discord\MessageTranslator;
use Illuminate\Database\Eloquent\Model;

class Connector extends Model
{
    protected $fillable = [
        'guild_id',
        'name',
        'channel_discord_id',
        'filter_gym_type',
        'filter_pokemon_type',
        'filter_gym_zone',
        'filter_gym_gym',
        'filter_ex_type',
        'filter_pokemon_level',
        'filter_pokemon_pokemon',
        'filter_source_type',
        'format',
        'custom_message_before',
        'custom_message_after',
        'auto_settings',
        'delete_after_end',
        'add_channel',
        'channel_category_discord_id',
        'channel_duration',
        'add_participants'
    ];
    protected $casts = [
        'filter_gym_zone' => 'array',
        'filter_gym_gym' => 'array',
        'filter_pokemon_level' => 'array',
        'filter_pokemon_pokemon' => 'array',
        'filter_source_type' => 'array',
        'auto_settings' => 'array',
        'add_channel' => 'boolean',
        'add_participants' => 'boolean',
    ];

    protected $appends = ['filtered_levels', 'filtered_pokemons', 'filtered_zones', 'filtered_gyms'];

    public $roles, $emojis, $channels;

    public function getFilteredLevelsAttribute()
    {
        if (empty($this->filter_pokemon_level)) {
            return [];
        }
        $levels = [];
        foreach ($this->filter_pokemon_level as $level_id) {
            $level = Helpers::getLevelObject($level_id);
            if ($level) {
                $levels[] = $level;
            }
        }
        return $levels;
    }

    public function getFilteredPokemonsAttribute()
    {
        if (empty($this->filter_pokemon_pokemon)) {
            return [];
        }
        $pokemons = [];
        foreach ($this->filter_pokemon_pokemon as $pokemon_id) {
            $pokemon = Pokemon::find($pokemon_id);
            if ($pokemon) {
                $pokemons[] = $pokemon;
            }
        }
        return $pokemons;
    }

    public function getFilteredZonesAttribute()
    {
        if (empty($this->filter_gym_zone)) {
            return [];
        }
        $zones = [];
        foreach ($this->filter_gym_zone as $zone_id) {
            $zone = Zone::find($zone_id);
            if ($zone) {
                $zones[] = $zone;
            }
        }
        return $zones;
    }

    public function getFilteredGymsAttribute()
    {
        if (empty($this->filter_gym_gym)) {
            return [];
        }
        $stops = [];
        foreach ($this->filter_gym_gym as $stop_id) {
            $stop = Stop::find($stop_id);
            if ($stop) {
                $stops[] = $stop;
            }
        }
        return $stops;
    }

    public function prepareMessage($raid)
    {
        if (empty($this->channel_discord_id)) return false;
        $guild = Guild::find($this->guild_id);
        //On initialise les infos discord
        $translator = MessageTranslator::to($guild)
            ->addGym($raid->getGym())
            ->addRaid($raid)
            ->addUser($raid->getLastUserAction()->getUser());

        //Récupération du message selon le format choisi
        if ($this->format == 'auto') {
            $content = '';
            $embed = $this->getEmbedMessage($raid, $guild, $translator);
        } elseif ($this->format == 'custom') {
            $content = $this->getCustomMessage($raid, $guild);
            $embed = [];
        } elseif ($this->format == 'both') {
            $content = $this->getCustomMessage($raid, $guild);
            $embed = $this->getEmbedMessage($raid, $guild, $translator);
        }
        return [
            'content' => $translator->translate($content),
            'embed' => $embed,
        ];
    }

    public function editMessage($raid, $message)
    {
        $data = $this->prepareMessage($raid);
        //On poste le message sur Discord et on log
        try {
            $discord = new DiscordClient(['token' => config('discord.token')]);
            $message = $discord->channel->editMessage(array(
                'message.id' => intval($message->discord_id),
                'channel.id' => intval($message->channel_discord_id),
                'content' => $data['content'],
                'embed' => $data['embed'],
            ));
        } catch (\Exception $e) {
            return false;
        }
    }

    public function postMessage($raid, $announce)
    {
        $data = $this->prepareMessage($raid);

        try {
            $discord = new DiscordClient(['token' => config('discord.token')]);
            $message = $discord->channel->createMessage(array(
                'channel.id' => intval($this->channel_discord_id),
                'content' => $data['content'],
                'embed' => $data['embed'],
            ));

            DiscordMessage::create([
                'relation_type' => 'raid',
                'relation_id' => $raid->id,
                'guild_id' => $this->guild_id,
                'connector_id' => $this->id,
                'discord_id' => $message['id'],
                'channel_discord_id' => $message['channel_id'],
                'to_delete_at' => ($this->delete_after_end) ? $raid->end_time : null,
            ]);

            if ($this->add_participants) {
                foreach (['👤', '🚁', '1️⃣', '2️⃣', '3️⃣', '✖️'] as $emoji) {
                    usleep(200000);
                    $result = $discord->channel->createReaction([
                        'channel.id' => intval($this->channel_discord_id),
                        'message.id' => intval($message['id']),
                        'emoji' => $emoji,
                    ]);
                }
            } elseif ($this->add_channel) {
                foreach (['#️⃣'] as $emoji) {
                    usleep(200000);
                    $result = $discord->channel->createReaction([
                        'channel.id' => intval($this->channel_discord_id),
                        'message.id' => intval($message['id']),
                        'emoji' => $emoji,
                    ]);
                }
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getCustomMessage($raid, $guild)
    {
        if ($raid->isFuture()) {
            return $this->custom_message_before;
        } else {
            return $this->custom_message_after;
        }
    }

    public function getEmbedMessage($raid, $guild, $translator)
    {

        //Gestion des infos du raid
        $description = [];
        $title = ($raid->egg_level == 7) ? 'Méga-raid' : 'Raid ' . $raid->egg_level . ' têtes';
        $img_url = "https://assets.profchen.fr/img/eggs/egg_" . $raid->egg_level . ".png";

        $startTime = new \DateTime($raid->start_time);
        $endTime = new \DateTime($raid->end_time);

        if ($raid->start_time) {
            $title .= ' à ' . $startTime->format('H\hi');
            $description[] = "Pop : de " . $startTime->format('H\hi') . " à " . $endTime->format('H\hi');
        }

        if ($raid->pokemon) {
            $title = html_entity_decode('Raid ' . $raid->pokemon->name_fr . ' jusqu\'à ' . $endTime->format('H\hi'));
            $img_url = $raid->pokemon->thumbnail_url;
        }

        $gymName = html_entity_decode($raid->getGym()->name);
        if ($raid->getGym()->zone_id) {
            $gymName = $raid->getGym()->zone->name . ' - ' . $gymName;
        }

        if (is_array($this->auto_settings)) {
            if (in_array('cp', $this->auto_settings) && $raid->pokemon) {
                $pokemon = RaidHelpers::getPokemonForCp($raid);
                $description[] = "Normal : CP entre " . $pokemon->cp['lvl20']['min'] . " et " . $pokemon->cp['lvl20']['max'] . "\r\n" .
                    "Boost Météo : CP entre " . $pokemon->cp['lvl25']['min'] . " et " . $pokemon->cp['lvl25']['max'];
            }
            if (in_array('arene_desc', $this->auto_settings) && !empty($raid->getGym()->description)) {
                $description[] = $translator->translate($raid->getGym()->description, $raid, $guild);
            }
        }

        //Gestion EX
        if ($raid->egg_level == 6) {
            $title = 'Raid EX le ' . $startTime->format('d/m') . ' à ' . $startTime->format('H\hi');
            if ($raid->channels) {
                foreach ($raid->channels as $channel) {
                    if ($channel->guild_id == $this->guild_id) {
                        $description[] = 'Vous pouvez vous organiser dans le salon <#' . $channel->channel_discord_id . '>';
                    }
                }
            }
        }

        //On formatte le embed
        $icon_url = ($raid->getGym()->ex) ? 'https://assets.profchen.fr/img/app/connector_gym_ex.png' : 'https://assets.profchen.fr/img/app/connector_gym.png';
        $data = array(
            'title' => $title,
            'description' => (!empty($description)) ? implode("\r\n\r\n", $description) : '',
            'color' => $this->getEggColor($raid->egg_level),
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

    public function getEggColor($eggLevel)
    {
        $colors = array(
            1 => 'de6591',
            2 => 'de6591',
            3 => 'efad02',
            4 => 'efad02',
            5 => '222',
            6 => '222',
            7 => 'efad02',
        );

        if (array_key_exists($eggLevel, $colors)) {
            return hexdec($colors[$eggLevel]);
        }
        return false;
    }
}