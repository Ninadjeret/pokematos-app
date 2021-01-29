<?php

namespace App\Core\Discord;;

use App\Models\Role;
use App\Core\Helpers;
use App\Models\Guild;
use RestCord\DiscordClient;
use Illuminate\Support\Facades\Log;

class MessageTranslator
{

  public function __construct(Guild $guild, $type)
  {
    $this->translatable = [];
    $this->guild = $guild;
    $this->type = $type;
    $this->addDiscordData();
  }

  public static function to(Guild $guild)
  {
    return new MessageTranslator($guild, 'to');
  }

  public static function From(Guild $guild)
  {
    return new MessageTranslator($guild, 'from');
  }

  protected function addDiscordData()
  {
    $discord = new DiscordClient(['token' => config('discord.token')]);
    $this->roles = $discord->guild->getGuildRoles(array(
      'guild.id' => intval($this->guild->discord_id)
    ));
    $this->channels = $discord->guild->getGuildChannels(array(
      'guild.id' => intval($this->guild->discord_id)
    ));
    $this->emojis = $discord->emoji->listGuildEmojis(array(
      'guild.id' => intval($this->guild->discord_id)
    ));
  }

  public function addGym($poi)
  {
    $this->addPoi($poi, 'arene');
    return $this;
  }

  public function addStop($poi)
  {
    $this->addPoi($poi, 'stop');
    return $this;
  }

  public function addPoi($poi, $type)
  {
    $this->poi = $poi;
    $this->translatable[$type . '_nom'] = $poi->niantic_name;
    $this->translatable[$type . '_nom_nettoye'] = Helpers::sanitize($poi->niantic_name);
    $this->translatable[$type . '_nom_custom'] = $poi->name;
    $this->translatable[$type . '_nom_custom_nettoye'] = Helpers::sanitize($poi->name);
    $this->translatable[$type . '_description'] = $poi->description;
    $this->translatable[$type . '_zone'] = (!empty($poi->zone)) ? $poi->zone->name : '';
    $this->translatable[$type . '_zone_nettoye'] = (!empty($poi->zone)) ? Helpers::sanitize($poi->zone->name) : '';
    $this->translatable[$type . '_ex'] = ($poi->ex) ? '[EX]' : '';
    $this->translatable[$type . '_gmaps'] = $poi->google_maps_url;

    $this->addPoiRelatedRoles($poi);
    return $this;
  }

  public function addRaid($raid)
  {
    $this->raid = $raid;
    $this->translatable['raid_pokemon'] = (!$raid->pokemon) ? '' : html_entity_decode($raid->pokemon->name_fr);
    $this->translatable['raid_pokemon_nettoye'] = (!$raid->pokemon) ? '' : Helpers::sanitize(html_entity_decode($raid->pokemon->name_fr));

    //Set diffretn format from raid level
    if ($raid->egg_level <= 5) {
      $this->translatable['raid_niveau'] = $raid->egg_level;
      $this->translatable['raid_debut'] = $raid->getStartTime()->format('H\hi');
      $this->translatable['raid_fin'] = $raid->getEndTime()->format('H\hi');
    } elseif ($raid->egg_level == 7) {
      $this->translatable['raid_niveau'] = 'Méga';
      $this->translatable['raid_debut'] = $raid->getStartTime()->format('H\hi');
      $this->translatable['raid_fin'] = $raid->getEndTime()->format('H\hi');
    } elseif ($raid->egg_level == 6) {
      $this->translatable['raid_niveau'] = 'EX';
      $this->translatable['raid_debut'] = $raid->getStartTime()->format('d/m/y à H\hi');
      $this->translatable['raid_fin'] = $raid->getEndTime()->format('d/m/y à H\hi');
    }

    $this->addPokemonRelatedRole($raid->pokemon);
    $this->addRaidGroup($raid->getGuildGroup($this->guild->id));
    return $this;
  }

  public function addUser($user)
  {
    $this->user = $user;
    if (empty($user)) {
      $this->translatable['utilisateur'] = '';
    } else {
      $this->translatable['utilisateur'] = $user->getNickname($this->guild->id);
    }
    return $this;
  }

  public function addCustomTranslatable($translatable)
  {
    foreach ($translatable as $name => $value) {
      $this->translatable[$name] = $value;
    }
    return $this;
  }

  protected function addPoiRelatedRoles($poi = null)
  {
    if (empty($poi)) {
      $this->translatable['role_poi_lie'] = '';
      $this->translatable['role_zone_liee'] = '';
      return;
    }

    $role_poi_lie = Role::where('gym_id', $poi->id)->first();
    $role_zone_liee = ($poi->zone) ? Role::where('zone_id', $poi->zone->id)->first() : false;

    $this->translatable['role_poi_lie'] = (!empty($role_poi_lie)) ? "@{$role_poi_lie->name}" : '';
    $this->translatable['role_zone_liee'] = (!empty($role_zone_liee)) ? "@{$role_zone_liee->name}" : '';
  }

  protected function addPokemonRelatedRole($pokemon = null)
  {
    if (empty($pokemon)) {
      $this->translatable['role_pokemon_lie'] = '';
      return;
    }
    $role_pokemon_lie = Role::where('pokemon_id', $pokemon->id)->first();
    $this->translatable['role_pokemon_lie'] = (!empty($role_pokemon_lie)) ? "@{$role_pokemon_lie->name}" : '';
  }

  protected function addRaidGroup($group = null)
  {
    $this->translatable['nb_participants'] = (!empty($group)) ? $group->getNbParticipants() : '0';
    $this->translatable['nb_participants_present'] = (!empty($group)) ? $group->getNbParticipants('present') : '0';
    $this->translatable['nb_participants_remote'] = (!empty($group)) ? $group->getNbParticipants('remote') : '0';
    $this->translatable['liste_participants'] = (!empty($group)) ? $group->getListeParticipants() : '';
  }

  public function translate($message = null)
  {
    if (empty($message)) return '';
    $this->message = $message;
    $this->translated = $this->message;
    if ($this->type == 'to') {
      $this->translateTo();
    } else {
      $this->translateFrom();
    }
    return $this->translated;
  }

  protected function translateTo()
  {
    foreach ($this->translatable as $pattern => $valeur) {
      $this->translated = str_replace('{' . $pattern . '}', $valeur, $this->translated);
    }
    $this->translateDiscordData();
    $this->sanitize();
  }

  protected function translateFrom()
  {
    //preg_match('/\<\!/i', $message, $out);
    preg_match_all("/<@&([0-9]*)>/", $this->translated, $mentions, PREG_SET_ORDER);

    if (!empty($mentions)) {
      foreach ($mentions as $mention) {
        $role = Role::where('discord_id', $mention[1])->first();
        if ($role) {
          $this->translated = str_replace($mention[0], '@' . $role->name, $this->translated);
        }
      }
    }
  }

  protected function translateDiscordData()
  {
    //Gestion des mentions
    if (strstr($this->translated, '@')) {
      foreach ($this->roles as $role) {
        if (strstr($this->translated, '@' . $role->name)) {
          $this->translated = str_replace('@' . $role->name, '<@&' . $role->id . '>', $this->translated);
        }
      }
    }
    if (!empty($this->user)) {
      if ($this->translatable['utilisateur'] && strstr($this->translated, '@' . $this->translatable['utilisateur'])) {
        $message = str_replace('@' . $this->translatable['utilisateur'], '<@!' . $this->user->discord_id . '>', $this->translated);
      }
    }

    //Gestion des salons #
    if (strstr($this->translated, '#')) {
      foreach ($this->channels as $channel) {
        if (strstr($this->translated, '#' . $channel->name)) {
          $this->translated = str_replace('#' . $channel->name, '<#' . $channel->id . '>', $this->translated);
        }
      }
    }

    //Gestion des emojis
    if (strstr($this->translated, ':')) {
      if (!empty($this->emojis)) {
        foreach ($this->emojis as $emoji) {
          if (strstr($this->translated, ':' . $emoji->name . ':')) {
            $this->translated = str_replace(':' . $emoji->name . ':', '<:' . $emoji->name . ':' . $emoji->id . '>', $this->translated);
          }
        }
      }
    }
  }

  protected function sanitize()
  {
    $this->translated = str_replace('@here', '{{here}}', $this->translated);
    $this->translated = str_replace('<@', '##<##', $this->translated);
    $this->translated = str_replace('@', '', $this->translated);
    $this->translated = str_replace('##<##', '<@', $this->translated);
    $this->translated = str_replace('{{here}}', '@here', $this->translated);
  }
}