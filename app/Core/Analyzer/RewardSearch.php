<?php

namespace App\Core\Analyzer;

use App\Models\QuestReward;
use Illuminate\Support\Facades\Log;

class RewardSearch
{

  /**
   *
   */
  function __construct()
  {
    $this->debug = false;
    $this->query = false;
    $this->rewards = QuestReward::all();
  }

  public function findReward($text)
  {
    $type = $this->findRewardType($text);
    $qty = $this->findRewardQty($text);

    if (!$type || !$qty) return false;

    $reward = QuestReward::where('type', $type)
      ->where('name', 'like', "$qty%")
      ->first();
    if ($reward) return $reward;
    return false;
  }

  public function findRewardType($text)
  {
    preg_match('/poussi[eéèê]re(s?)/i', $text, $matches);
    if (!empty($matches)) return 'stardust';

    preg_match('/poke[eéèê](-?)ball/i', $text, $matches);
    if (!empty($matches)) return 'pokeball';

    preg_match('/super(-?)ball/i', $text, $matches);
    if (!empty($matches)) return 'superball';

    preg_match('/hyper(-?)ball/i', $text, $matches);
    if (!empty($matches)) return 'hyperball';

    preg_match('/nanana/i', $text, $matches);
    if (!empty($matches)) return 'nanana';

    preg_match('/nanab/i', $text, $matches);
    if (!empty($matches)) return 'nanab';

    return false;
  }

  public function findRewardQty($text)
  {
    preg_match('/\d+/i', $text, $matches);
    if (!empty($matches)) return $matches[0];
    return false;
  }
}