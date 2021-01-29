<?php

namespace App\Core\Rankings;

use App\User;
use App\Models\City;
use App\Models\Guild;
use App\Models\UserAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserRanking
{

  public function __construct()
  {
    $this->types = [];
    $this->user = false;
    $this->city = false;
    $this->guild = false;
    $this->start = false;
    $this->end = false;
    $this->limit = false;
    return $this;
  }

  public static function forRaids()
  {

    $ranking = new UserRanking();
    $ranking->types = ['raid-create', 'raid-update'];
    return $ranking;
  }

  public static function forQuests()
  {

    $ranking = new UserRanking();
    $ranking->types = ['quest-create', 'quest-update'];
    return $ranking;
  }

  public static function forRocket()
  {

    $ranking = new UserRanking();
    $ranking->types = ['rocket-invasion-create', 'rocket-invasion-update'];
    return $ranking;
  }

  public static function forAll()
  {

    $ranking = new UserRanking();
    $ranking->types = ['raid-create', 'raid-update', 'quest-create', 'quest-update', 'rocket-invasion-create', 'rocket-invasion-update'];
    return $ranking;
  }

  public function forUser($user)
  {
    $this->user = ($user instanceof User) ? $user : User::find($user);
    return $this;
  }

  public function forPeriod($start, $end)
  {
    $this->start = ($start instanceof \DateTime) ? $start : \DateTime::createFromFormat('Y-m-d', $start);
    $this->end = ($end instanceof \DateTime) ? $end : \DateTime::createFromFormat('Y-m-d', $end);
    return $this;
  }

  public function forCity($city)
  {
    $this->city = ($city instanceof City) ? $city : City::find($city);
    return $this;
  }

  public function forGuild($guild)
  {
    $this->guild = ($guild instanceof Guild) ? $guild : Guild::find($guild);
    return $this;
  }

  public function setLimit($num)
  {
    $this->limit = $num;
    return $this;
  }

  public function getComplete()
  {
    return $this->get(false);
  }

  public function getShort()
  {
    return $this->get(true);
  }

  private function get($short = false)
  {
    $query = UserAction::whereIn('type', $this->types)
      ->where('date', '>', $this->start->format('Y-m-d') . ' 00:00:00')
      ->where('date', '<', $this->end->format('Y-m-d') . ' 23:59:59')
      ->where('user_id', '!=', 0)
      ->groupBy('user_id', 'city_id')
      ->select('user_id', 'city_id', DB::raw('count(*) as total'))
      ->orderBy('total', 'DESC');

    if ($this->city) $query->where('city_id', $this->city->id);
    if ($this->guild) $query->where('guild_id', $this->guild->id);

    if ($this->limit) $query->limit($this->limit);

    $ranking = $query->get();

    $pos = 0;
    $user_position = 0;
    foreach ($ranking as &$line) {
      if ($this->user && $line['user_id'] == $this->user->id) $user_position = $pos;

      $user = User::find($line['user_id']);
      $city = City::find($line['city_id']);
      $guild_ids = $city->getGuildsIds();

      $line['user'] = $user->setAppends([])->toArray();
      $line['user_nickname'] = count($guild_ids) === 1 ? $user->getNickname($guild_ids[0]) : $user->name;
      unset($line['user_id']);

      $line['city'] = $city->name;
      unset($line['city_id']);

      $pos++;
      $line['rank'] = $pos;
    }
    $ranking = $ranking->toArray();

    if ($short) {
      $start = ($user_position == 0) ? 0 : $user_position - 1;
      $ranking = array_slice($ranking, $start, 3);
    }

    return $ranking;
  }
}