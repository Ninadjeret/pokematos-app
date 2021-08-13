<?php

namespace App\Models;

use App\Models\Pokemon;
use Illuminate\Database\Eloquent\Model;

class QuestReward extends Model
{
    protected $table = 'quest_rewards';
    protected $fillable = ['name', 'type', 'sstype', 'qty'];
    protected $appends = ['thumbnail_url'];

    public function getThumbnailUrlAttribute() {
        if( $this->type == 'megaenergy' ) {
            return  asset("/storage/img/pokemon/energy/mega_energy_{$this->sstype}.png");
        }
        return 'https://assets.profchen.fr/img/rewards/reward_'.$this->id.'.png';
    }
}
