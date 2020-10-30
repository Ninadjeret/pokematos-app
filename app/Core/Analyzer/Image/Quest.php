<?php

namespace App\Core\Analyzer\Image;

use App\Core\Analyzer\ImageAnalyzer;
use App\Core\Analyzer\Traits\Textable;
use App\Core\Analyzer\Traits\Imageable;

class Quest extends ImageAnalyzer
{

  use Imageable, Textable;

  public function __construct($args)
  {
    $this->args = $args;
    $this->type = 'quest';
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
    $result = $this->gymSearch->findGym($this->ocr);
    //$result = PoiSearch::addStops()->find($this->ocr);
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
    $result = $this->pokemonSearch->findPokemonFromstring($this->source_text, 90);
    //$result = PokemonSearch::init()->addQuestPokemons()->findFromstring($this->source_text, 90);
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
}