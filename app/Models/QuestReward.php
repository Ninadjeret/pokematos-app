<?php

namespace App\Models;

use App\Models\Pokemon;
use Illuminate\Database\Eloquent\Model;

class QuestReward extends Model
{
    protected $table = 'quest_rewards';
    protected $fillable = ['name', 'type', 'sstype', 'qty'];
    protected $appends = ['thumbnails'];

    public function getThumbnailsAttribute() {
        if( $this->type == 'megaenergy' ) {
            return (object) [
                'base' => asset("/storage/img/pokemon/energy/megaenergy_{$this->sstype}.png"),
                'quest' => asset("/storage/img/pokemon/energyquest/megaenergy_{$this->sstype}.png"),
            ];
        }
        return (object) [
            'base' => asset("storage/img/items/base/item_{$this->type}.png"),
            'quest' => asset("storage/img/items/quest/item_{$this->type}.png"),
        ];
    }
}
