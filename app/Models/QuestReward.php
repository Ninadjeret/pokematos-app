<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestReward extends Model
{
    protected $table = 'quest_rewards';
    protected $appends = ['thumbnail_url'];

    public function getThumbnailUrlAttribute() {
        return 'https://assets.profchen.fr/img/rewards/reward_'.$this->type.'.png';
    }
}
