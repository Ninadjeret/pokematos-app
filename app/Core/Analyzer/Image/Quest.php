<?php

namespace App\Core\Analyzer\Image;

use App\Core\Analyzer\Analyzer;
use App\Core\Analyzer\GymSearch;
use App\Core\Analyzer\PokemonSearch;
use App\Core\Analyzer\Traits\Textable;
use App\Core\Analyzer\Traits\Imageable;

class Quest extends Analyzer
{

  use Imageable, Textable;

  public function __construct($args)
  {
    $this->args = $args;
    $this->type = 'quest';
    $this->source_type = 'img';
    $this->crop_width_ratio = 0;
    $this->result =
      (object) array(
        'gym' => false,
        'gym_probability' => 0,
        'reward'   => false,
        'date' => false,
        'type' => 'quest',
        'error' => false,
        'logs' => '',
      );
    parent::__construct();
  }

  public function run()
  {
    if ($this->imageData->type == 'quest') {
      $this->ocr = $this->MicrosoftOCR->read($this->imageData->url_ocr);
      $this->_log($this->ocr);

      $line_num = 0;
      $ocr = [];
      foreach( $this->ocr as $line ) {
        $line_num++;
        if( strlen($line) < 4 && $line_num === 1  ) continue;
        if( $line == 'Ce PokeStop est trop loin.' ) continue;
        $ocr[] = $line;
      }
      $this->ocr = $ocr;
      $this->result->gym = $this->getGym();
      $this->result->date = date('Y-m-d');
      $this->result->reward = $this->getReward();
    }
  }

  private function getImageType()
  {
    return 'quest';
  }

  function getGym()
  {
    $result = GymSearch::init($this->guild)
      ->addStops()
      ->setAccuracy($this->guild->settings->raidreporting_gym_min_proability)
      ->find(implode(' ',$this->ocr));
    if ($result) {
      if ($this->debug) $this->_log('Gym finded in database : ' . $result->gym->name);
      return $result->gym;
    }
    if ($this->debug) $this->_log('Nothing found in database :(');
    $this->result->error = "L'arène n'a pas été trouvée";
    return false;
  }

  public function getReward()
  {
    $result = PokemonSearch::init()->addQuestPokemon()->setAccuracy(90)->find($this->source_text);
    if ($result) {
      return (object) [
        'type' => 'pokemon',
        'id' => $result->pokemon->id
      ];
    }

    $result = $this->rewardSearch->findReward($this->source_text);
    if ($result) {
      return (object) [
        'type' => 'reward',
        'id' => $result->id
      ];
    }

    return false;
  }

  public function getLogResult()
  {
    return [
      'type' => $this->result->type,
      'gym' => $this->result->gym,
      'gym_probability' => $this->result->gym_probability,
      'url' => $this->source_url,
      'ocr' => (property_exists($this, 'ocr')) ? implode(' ', $this->ocr) : '',
    ];
  }

}
