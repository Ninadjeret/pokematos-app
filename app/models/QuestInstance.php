<?php

namespace App\Models;

use App\Models\Quest;
use Illuminate\Database\Eloquent\Model;

class QuestInstance extends Model
{
    protected $fillable = ['date', 'quest_id', 'city_id', 'gym_id'];
    protected $appends = ['quest'];
    protected $hidden = ['quest_id'];

    public function getQuestAttribute() {
        $quest = Quest::find($this->quest_id);
        if( !empty( $quest ) ) return $quest;
        return false;
    }
}
