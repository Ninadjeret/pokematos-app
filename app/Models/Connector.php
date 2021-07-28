<?php

namespace App\Models;

use App\Models\Stop;
use App\Models\Zone;
use App\Core\Helpers;
use App\Models\Guild;
use App\Models\Pokemon;
use App\Models\RaidGroup;
use RestCord\DiscordClient;
use App\Models\DiscordMessage;
use App\Core\Raids\RaidHelpers;
use Illuminate\Support\Facades\Log;
use App\Core\Discord\MessageTranslator;
use Illuminate\Database\Eloquent\Model;
use App\Core\Discord\Messages\RaidEmbed;

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


        //Récupération du message selon le format choisi
        if ($this->format == 'auto') {
            $content = '';
            $embed = $this->getEmbedMessage($raid, $guild);
        } elseif ($this->format == 'custom') {
            $content = $this->getCustomMessage($raid, $guild);
            $embed = [];
        } elseif ($this->format == 'both') {
            $content = $this->getCustomMessage($raid, $guild);
            $embed = $this->getEmbedMessage($raid, $guild);
        }
        return [
            'content' => $content,
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
        $guild = Guild::find($this->guild_id);

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
                'type' => 'announce',
                'connector_id' => $this->id,
                'discord_id' => $message['id'],
                'channel_discord_id' => $message['channel_id'],
                'to_delete_at' => ($this->delete_after_end) ? $raid->end_time : null,
            ]);

            if ($this->add_participants) {
                $icons = ['👤', '🚁', '🎟️', '❌'];
                if( $guild->settings->raidorga_nb_players ) $icons = ['👤', '🚁', '🎟️', '1️⃣', '2️⃣', '3️⃣', '❌'];
                foreach ($icons as $emoji) {
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

        $translator = MessageTranslator::to($guild)
            ->addGym($raid->getGym())
            ->addRaid($raid)
            ->addUser($raid->getLastUserAction()->getUser());

        if ($raid->isFuture()) {
            return $translator->translate($this->custom_message_before);
        } else {
            return $translator->translate($this->custom_message_after);
        }
    }

    public function getEmbedMessage($raid, $guild)
    {
        $embed = RaidEmbed::forRaid($raid)
            ->forGuild($guild)
            ->setSettings($this->auto_settings)
            ->get();
        return $embed;
    }

}
