<?php

namespace App\Traits;

use App\Models\Guild;
use Illuminate\Support\Facades\Log;

trait Participable
{

  public function getParticipants($guild_id, $type = false)
  {
    $group = $this->groups->where('guild_id', $guild_id)->first();
    if (empty($group)) return false;

    $participants = $group->participants;
    if ($type) $participants->where('type', $type);
    return $participants;
  }

  public function getNbParticipants($guild_id, $type = false)
  {
    $participants = $this->getParticipants($guild_id, $type);
    if ($type) $participants->where('type', $type);
    return $participants->count();
  }
}