<?php

namespace App\Core\Analyzer\Image;

use App\Core\Analyzer\Analyzer;
use App\Core\Analyzer\GymSearch;
use Illuminate\Support\Facades\Log;
use App\Core\Analyzer\EggClassifier;
use App\Core\Analyzer\PokemonSearch;
use App\Core\Analyzer\Traits\Imageable;

class Raid extends Analyzer
{

  use Imageable;

  public function __construct($args)
  {
    $this->args = $args;
    $this->type = 'raid';
    $this->source_type = 'img';
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

    if ($this->imageData->type == 'egg') {
      $this->ocr = $this->MicrosoftOCR->read($this->imageData->url_ocr);
      $this->_log($this->ocr);
      $this->result->type = 'egg';
      $this->result->gym = $this->getGym();
      $this->result->date = $this->getTime();
      $this->result->eggLevel = $this->getEggLevelV2();
    } elseif ($this->imageData->type == 'pokemon') {
      $this->ocr = $this->MicrosoftOCR->read($this->imageData->url_ocr);
      $this->_log($this->ocr);
      $this->result->type = 'pokemon';
      $this->result->date = $this->getTime();
      $this->result->pokemon = $this->getPokemon();
      $this->result->gym = $this->getGym();
      if ($this->result->pokemon) {
        $this->result->eggLevel = $this->result->pokemon->boss_level;
      }
    } elseif ($this->imageData->type == 'ex') {
      $this->ocr = $this->MicrosoftOCR->read($this->imageData->url_ocr);
      $this->_log($this->ocr);
      $this->result->type = 'ex';
      $this->result->gym = $this->getExGym();
      $this->result->date = $this->getExTime();
      $this->result->eggLevel = 6;
    }
  }

  public function getLogResult()
  {
    return [
      'type' => $this->result->type,
      'gym' => $this->result->gym,
      'gym_probability' => $this->result->gym_probability,
      'date' => $this->result->date,
      'pokemon' => $this->result->pokemon,
      'pokemon_probability' => $this->result->pokemon_probability,
      'egg_level' => $this->result->eggLevel,
      'url' => $this->source_url,
      'ocr' => (property_exists($this, 'ocr')) ? implode(' ', $this->ocr) : '',
    ];
  }

  private function getImageType()
  {

    $image = imagecreatefromjpeg($this->imageData->path);

    //Check for raidex
    if ($this->debug) $this->_log('---------- Check if image is Raid EX invit ----------');
    $matching_points = 0;
    foreach ($this->coordinates->forImgTypeEx() as $coords) {
      $rgb = $this->colorPicker->pickColor($image, $coords->x, $coords->y);
      if ($this->colorPicker->isExBackground($rgb)) {
        $matching_points++;
      }
    }
    if ($matching_points == 4) {
      if ($this->debug) $this->_log('Great ! Img seems to be an EX invit');
      return 'ex';
    }


    //Check for Future Raid 1 & 2 & 3
    if ($this->debug) $this->_log('---------- Check if image is Raid UserAction ----------');
    $ys = [$this->coordinates->forImgTypeEgg()->y, $this->coordinates->forImgTypeEgg()->y * 1.05, $this->coordinates->forImgTypeEgg()->y * 0.95];
    foreach ($ys as $y) {
      $rgb = $this->colorPicker->pickColor($image, $this->coordinates->forImgTypeEgg()->x, $y);
      if ($this->colorPicker->isFutureTimerColor($rgb)) {
        if ($this->debug) $this->_log('Great ! Img seems to include an egg');
        return 'egg';
      }
    }

    //Check for active Raid - v1
    if ($this->debug) $this->_log('IMG does not seem to be an egg. Trying to check if it includes a pokemon');
    $ys = [$this->coordinates->forImgTypePokemon()->y, $this->coordinates->forImgTypePokemon()->y * 1.05, $this->coordinates->forImgTypePokemon()->y * 0.95];
    foreach ($ys as $y) {
      $rgb = $this->colorPicker->pickColor($image, $this->coordinates->forImgTypePokemon()->x, $y);
      if ($this->colorPicker->isActiveTimerColor($rgb)) {
        if ($this->debug) $this->_log('Great ! Img seems to include a pokemon');
        return 'pokemon';
      }
    }

    //else
    $this->result->error = 'L\'image n\'a pas été comprise comme une image de raid';
    imagedestroy($image);
    return false;
  }

  function getGym()
  {
    $result = GymSearch::init($this->guild)
      ->addGyms()
      ->setAccuracy($this->guild->settings->raidreporting_gym_min_proability)
      ->find($this->ocr[0]);
    if ($result) {
      if ($this->debug) $this->_log('Gym finded in database : ' . $result->gym->name . '(' . $result->probability . '%)');
      $this->result->gym_probability = $result->probability;
      return $result->gym;
    }
    if ($this->debug) $this->_log('Nothing found in database :(');
    $this->result->error = "L'arène n'a pas été trouvée";
    return false;
  }

  function getExGym()
  {
    $value = implode(' ', $this->ocr);
    $result = GymSearch::init($this->guild)
      ->addGyms()
      ->setAccuracy($this->guild->settings->raidreporting_gym_min_proability)
      ->find($value);
    if ($result) {
      if ($this->debug) $this->_log('Gym finded in database : ' . $result->gym->name . '(' . $result->probability . '%)');
      $this->result->gym_probability = $result->probability;
      return $result->gym;
    }
    if ($this->debug) $this->_log('Nothing found in database :(');
    $this->result->error = "L'arène n'a pas été trouvée";
    return false;
  }

  function getPokemon()
  {
    $cp = $this->MicrosoftOCR->cp_line;
    $result = PokemonSearch::init()->addRaidPokemon()->setAccuracy(90)->find($this->ocr, $cp);
    if ($result) {
      if ($this->debug) $this->_log('Pokemon finded in database : ' . $result->pokemon->name_fr . '(' . $result->probability . '%)');
      $this->result->pokemon_probability = $result->probability;
      return $result->pokemon;
    }

    if ($this->debug) $this->_log('Nothing found in database :(');
    $this->result->error = "Aucun Pokémon trouvé";
    return false;
  }

  /**
   *
   * @param type $souce
   * @return boolean
   */

  public function getEggLevelV2()
  {
    $image = imagecreatefromjpeg($this->imageData->path);
    $result = EggClassifier::getLevel($image);
    imagedestroy($image);
    return $result;
    $this->result->error = "Le niveau du raid n'a pas été trouvé";
    return false;
  }

  function getExTime()
  {
    $date = false;
    foreach ($this->ocr as $line) {
      if (preg_match('/[0-9][0-9]:[0-9][0-9] - [0-9][0-9]:[0-9][0-9]/i', $line)) {
        $date_els = explode(' - ', $line);
        $date = $date_els[0];
      }
    }

    if ($date) {
      $date_els = explode(' ', $date);
      $year = date('Y');
      $day = (strlen($date_els[0]) === 1) ? '0' . $date_els[0] : $date_els[0];
      $month = str_replace(
        ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'aout', 'septembre', 'octobre', 'novembre', 'décembre'],
        ['01', '02', '03', '04', '05', '06', '07', '08', '08', '09', '10', '11', '12'],
        $date_els[1]
      );
      $minutes = $date_els[2];
      return "{$year}-{$month}-{$day} {$minutes}:00";
    }

    return false;
  }

  function getTime()
  {
    $minutes = false;
    foreach ($this->ocr as $line) {
      if (preg_match('/^[0-9]:[0-9][0-9]:[0-9][0-9]/i', $line)) {
        $timer = explode(':', $line);
        $minutes = $timer[1];
      }
    }

    if ($minutes) {
      $date = new \DateTime();
      $date->setTimezone(new \DateTimeZone('Europe/Paris'));
      if ($this->imageData->type == 'egg') {
        $date->modify('+' . $minutes . ' minutes');
      } else {
        $minutes = 45 - $minutes;
        $date->modify('-' . $minutes . ' minutes');
      }
      return $date->format('Y-m-d H:i:s');
    }

    $this->result->error = "Aucun timing trouvé dans la capture";
    return false;
  }
}