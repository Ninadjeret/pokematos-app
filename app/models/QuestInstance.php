<?php

namespace App\Models;

use App\Models\Quest;
use App\Models\QuestMessage;
use Illuminate\Database\Eloquent\Model;

class QuestInstance extends Model
{
    protected $fillable = ['date', 'quest_id', 'city_id', 'gym_id', 'name'];
    protected $appends = ['quest', 'messages', 'reward_type'];
    protected $hidden = ['quest_id', 'reward_id'];

    public function getQuestAttribute() {
        $quest = Quest::find($this->quest_id);
        if( !empty( $quest ) ) return $quest;
        return false;
    }

    public function getRewardAttribute() {
        if( $this->reward_type == 'pokemon' ) {
            return Pokemon::find($this->reward_id);
        }
        return QuestReward::find($this->reward_id);
    }

    public function getStop() {
        return Stop::find( $this->gym_id );
    }

    public function getLastAnnounce() {
        $annonce = Announce::where('quest_instance_id', $this->id)
            ->orderBy('created_at', 'desc')
            ->first();
        return $annonce;
    }

    public function getAnnounces() {
        $annonces = Announce::where('quest_instance_id', $this->id)
            ->orderBy('created_at', 'asc')
            ->get();
        return $annonces;
    }

    public function getMessagesAttribute() {
        $messages = QuestMessage::where('quest_instance_id', $this->id)->get();
        if( $messages ) {
            return $messages;
        }
        return [];
    }
}
