<?php

namespace App\Core\Analyzer\Text;

use App\Core\Analyzer\Analyzer;
use App\Core\Analyzer\GymSearch;
use Illuminate\Support\Facades\Log;
use App\Core\Analyzer\PokemonSearch;
use App\Core\Analyzer\Traits\Textable;

class Raid extends Analyzer
{
  use Textable;

  public function __construct($args)
  {
    $this->args = $args;
    $this->type = 'raid';
    $this->source_type = 'text';
    $this->crop_width_ratio = 0.2;
    $this->result
      = (object) array(
        'type' => false,
        'gym' => false,
        'gym_probability' => 0,
        'eggLevel' => false,
        'pokemon'   => false,
        'pokemon_probability' => 0,
        'date' => false,
        'error' => false,
        'logs' => '',
      );
    parent::__construct();
  }

  public function run()
  {

    if ($this->isValid()) {
      $this->result->date = $this->getTime();
      $this->result->gym = $this->getGym();
      $this->result->eggLevel = $this->getEggLevel();
      $this->result->pokemon = $this->getPokemon();
      if ($this->result->pokemon) {
        $this->result->eggLevel = $this->result->pokemon->boss_level;
      }
    }
  }

  public function getLogResult()
  {
    return
      [
        'gym' => $this->result->gym,
        'gym_probability' => $this->result->gym_probability,
        'date' => $this->result->date,
        'pokemon' => $this->result->pokemon,
        'pokemon_probability' => $this->result->pokemon_probability,
        'egg_level' => $this->result->eggLevel,
        'text' => $this->source_text,
      ];
  }

  public function isValid()
  {
    $prefixes = $this->guild->settings->raidreporting_text_prefixes;
    if (empty($prefixes)) {
      $this->result->error = 'Aucun préfixe n \'est renseigné';
      return false;
    }
    foreach ($prefixes as $prefix) {
      $prefix = trim($prefix);
      if (strpos($this->source_text, $prefix) === 0) {
        return true;
      }
    }
    $this->result->error = 'Ce texte ne semble pas être une annonce de raid';
    return false;
  }

  public function getEggLevel()
  {

    //Recherche de base avec tetes
    preg_match('/\d(\s?)t[eéèê]te(s?)/i', $this->source_text, $matches);
    if (!empty($matches)) {
      return (int) preg_replace('`[^0-9]`', '', $matches[0]);
    }

    //Recherche de la version simplifiée
    preg_match('/\d(\s?)t\b/i', $this->source_text, $matches);
    if (!empty($matches)) {
      return (int) preg_replace('`[^0-9]`', '', $matches[0]);
    }

    //Recherche de la version simplifiée inversée
    preg_match('/t\d/i', $this->source_text, $matches);
    if (!empty($matches)) {
      return (int) preg_replace('`[^0-9]`', '', $matches[0]);
    }

    if (stristr($this->source_text, 'mega') || stristr($this->source_text, 'méga')) {
      return 7;
    }

    return false;
  }

  public function getTime()
  {

    preg_match('/(jusqu\'a|jusqu\'à|jusqu’a|jusqu’à|depop|dépop|depop à|depop a|dépop à|dépop à|fin à|fin a)\s(\d?)\d(\s?)[h:](\s?)(\d?\d?)\b/i', $this->source_text, $dates_fin);
    if (!empty($dates_fin)) {
      return $this->getTimeFromEndDate($dates_fin[0]);
    }

    preg_match('/(\d?)\d(\s?)[h:](\s?)(\d?\d?)\b/i', $this->source_text, $dates_debut);
    if (!empty($dates_debut)) {
      return $this->getTimeFromStartDate($dates_debut[0]);
    }

    preg_match('/(d(e|é)pope?\sdans|reste(\sencore)?|pour(\sencore)?)\s\d?\d\s?(min|minute|minutes)/i', $this->source_text, $delais_fin);
    if (!empty($delais_fin)) {
      return $this->getTimeFromEndDelay($delais_fin[0]);
    }

    preg_match('/(dans|d\'ici)\s\d?\d\s?(min|minute|minutes)/i', $this->source_text, $delais_debut);
    if (!empty($delais_debut)) {
      return $this->getTimeFromStartDelay($delais_debut[0]);
    }
  }

  public function getTimeFromStartDate($date_string)
  {
    $dates = (strstr($date_string, ':')) ? explode(':', $date_string) : explode('h', $date_string);
    $hours = preg_replace('`[^0-9]`', '', $dates[0]);
    $minutes = preg_replace('`[^0-9]`', '', $dates[1]);
    if (empty($minutes)) $minutes = '00';
    $date = \DateTime::createFromFormat('H:i', $hours . ':' . $minutes);
    return $date->format('Y-m-d H:i:s');
  }

  public function getTimeFromEndDate($date_string)
  {
    $dates = (strstr($date_string, ':')) ? explode(':', $date_string) : explode('h', $date_string);
    $hours = preg_replace('`[^0-9]`', '', $dates[0]);
    $minutes = preg_replace('`[^0-9]`', '', $dates[1]);
    $date = \DateTime::createFromFormat('H:i', $hours . ':' . $minutes);
    $date->modify('- 45 minutes');
    return $date->format('Y-m-d H:i:s');
  }

  public function getTimeFromStartDelay($date_string)
  {
    $minutes = preg_replace('`[^0-9]`', '', $date_string);
    $date = new \DateTime();
    $date->modify('+ ' . $minutes . ' minutes');
    return $date->format('Y-m-d H:i:s');
  }

  public function getTimeFromEndDelay($date_string)
  {
    $minutes = preg_replace('`[^0-9]`', '', $date_string);
    $minutes = 45 - $minutes;
    $date = new \DateTime();
    $date->modify('- ' . $minutes . ' minutes');
    return $date->format('Y-m-d H:i:s');
  }

  function getGym()
  {
    $result = GymSearch::init($this->guild)
      ->addGyms()
      ->setAccuracy($this->guild->settings->raidreporting_gym_min_proability)
      ->find($this->source_text);
    if ($result) {
      if ($this->debug) $this->_log('Gym finded in database : ' . $result->gym->name);
      $this->result->gym_probability = $result->probability;
      return $result->gym;
    }
    $this->result->error = "L'arène n'a pas été trouvée";
    if ($this->debug) $this->_log('Nothing found in database :(');
  }

  function getPokemon()
  {
    $query = $this->source_text;
    $result = PokemonSearch::init()->addQuestPokemon()->setAccuracy(70)->find($query);
    if ($result) {
      if ($this->debug) $this->_log('Pokemon finded in database : ' . $result->pokemon->name_fr);
      $this->result->pokemon_probability = $result->probability;
      return $result->pokemon;
    }
    if (!$this->result->eggLevel) $this->result->error = "Aucun Pokémon trouvé";
    if ($this->debug) $this->_log('Nothing found in database :(');
    return false;
  }
}