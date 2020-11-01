<?php

namespace App\Core\Tools;

use App\Models\Pokemon;

class GameMaster
{

  public static function toUpdate()
  {
    $to_add = [];
    $to_update = [];

    $game_master = file_get_contents('https://raw.githubusercontent.com/PokeMiners/game_masters/master/latest/latest.json');
    $game_master = json_decode($game_master);

    $names_fr = file_get_contents('https://raw.githubusercontent.com/sindresorhus/pokemon/master/data/fr.json');
    $names_fr = json_decode($names_fr, true);

    foreach ($game_master as $node) {
      if (!isset($node->data->pokemonSettings) || empty($node->data->pokemonSettings)) continue;
      if (strstr($node->templateId, '_NORMAL')) continue;
      if (strstr($node->templateId, '_PURIFIED')) continue;
      if (strstr($node->templateId, '_SHADOW')) continue;
      if (strstr($node->templateId, '_FALL_2019')) continue;
      if (strstr($node->templateId, '_COSTUME_')) continue;
      if (strstr($node->templateId, '_2019')) continue;
      if (strstr($node->templateId, '_2020')) continue;
      if (strstr($node->templateId, '_2021')) continue;

      $pokedex_id = substr($node->templateId, 2, 3);
      $name_ocr = (isset($names_fr[(int)$pokedex_id])) ? $names_fr[(int)$pokedex_id - 1] : null;
      $form_id = (isset($node->data->pokemonSettings->form)) ? $node->data->pokemonSettings->form : '00';

      $forms = [
        'ALOLA' => 'd\'Alola',
        'SPEED' => 'Vitesse',
        'ATTACK' => 'Attaque',
        'DEFENSE' => 'Défense',
        'PLANT' => 'Plante',
        'SANDY' => 'Sable',
        'TRASH' => 'Déchet',
        'RAINY' => 'Pluie',
        'SNOWY' => 'Neige',
        'SUNNY' => 'Soleil',
        'OVERCAST' => 'Couvert',
        'GALARIAN' => 'de Galar',
      ];

      $name_fr = $name_ocr;
      if (!empty($form_id) && $form_id != '00') {
        foreach ($forms as $form => $label) {
          if (strstr($node->templateId, $form)) {
            $name_fr = $name_ocr . ' ' . $label;
          }
        }
      }

      //On transforme les IDS des formes en numéro pour correspondre aux sprites officielles
      if (strstr($form_id, 'GALARIAN')) {
        $form_id = '31';
      }
      if (strstr($form_id, 'ALOLA')) {
        $form_id = '61';
      }

      $data = [
        'pokedex_id' => $pokedex_id,
        'niantic_id'  => $node->templateId,
        'name_fr'   => $name_fr,
        'name_ocr'   => $name_ocr,
        'form_id'  => $form_id,
        'base_att'  => $node->data->pokemonSettings->stats->baseAttack,
        'base_def'  => $node->data->pokemonSettings->stats->baseDefense,
        'base_sta'  => $node->data->pokemonSettings->stats->baseStamina,
        'parent_id' => null,
      ];

      $pokemon = self::find($data['niantic_id']);
      if ($pokemon) {
        $diff = self::compare($pokemon, $data);
        if ($diff > 0) {
          $to_update[$data['niantic_id']] = $data;
        }
      } else {
        $to_add[$data['niantic_id']] = $data;
      }

      //Gestion des Méga
      if (isset($node->data->pokemonSettings->obTemporaryEvolutions)) {
        foreach ($node->data->pokemonSettings->obTemporaryEvolutions as $mega) {
          $name = 'Méga-' . $name_fr;
          $suffix_id = str_replace('TEMP_EVOLUTION', '', $mega->obTemporaryEvolution);
          $suffixe = str_replace('TEMP_EVOLUTION_MEGA', '', $mega->obTemporaryEvolution);
          if (!empty($suffixe)) $name .= ' ' . str_replace('_', '', $suffixe);
          $form_id = ($suffixe == '_Y') ? 52 : 51;
          $data = [
            'pokedex_id' => $pokedex_id,
            'niantic_id'  => $node->templateId . $suffix_id,
            'name_fr'   => $name,
            'name_ocr'   => $name,
            'form_id'  => $form_id,
            'base_att'  => $mega->stats->baseAttack,
            'base_def'  => $mega->stats->baseDefense,
            'base_sta'  => $mega->stats->baseStamina,
            'parent_id' => null,
          ];

          $pokemon = self::find($data['niantic_id']);
          if ($pokemon) {
            $diff = self::compare($pokemon, $data);
            if ($diff > 0) {
              $to_update[$data['niantic_id']] = $data;
            }
          } else {
            $to_add[$data['niantic_id']] = $data;
          }
        }
      }
    }

    return (object) [
      'to_add' => $to_add,
      'to_update' => $to_update
    ];
  }

  public static function find($niantic_id)
  {
    $pokemon = Pokemon::where('niantic_id', $niantic_id)->first();
    if ($pokemon) {
      return $pokemon;
    }
    return false;
  }

  public static function compare($pokemon, $data)
  {
    $diff = 0;
    foreach (['base_att', 'base_def', 'base_sta'] as $spec) {
      if (isset($data[$spec]) && $data[$spec] != $pokemon->$spec) {
        $diff++;
      }
    }
    return $diff;
  }
}