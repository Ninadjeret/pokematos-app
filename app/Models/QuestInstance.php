<?php

namespace App\Models;

use App\Models\Quest;
use App\Models\Announce;
use App\Models\QuestMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class QuestInstance extends Model
{
    protected $fillable = ['date', 'quest_id', 'name', 'reward_id', 'reward_type', 'city_id', 'gym_id'];
    protected $appends = ['messages', 'reward', 'quest'];
    protected $hidden = ['reward_id'];

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

    public static function add( $params ) {

        $args = [
            'city_id' => $params['city_id'],
            'gym_id'  => $params['gym_id'],
            'date'    => date('Y-m-d 00:00:00'),
        ];

        if( isset($params['quest_id']) && $params['quest_id'] ) {
            $quest = Quest::find($params['quest_id']);
            if( $quest ) {
                $args['quest_id'] = $params['quest_id'];
                $args['name'] = $quest->name;
                if( !empty($quest->rewards) && count($quest->rewards) === 1 ) {
                    if( !empty( $quest->pokemon_ids ) ) {
                        $args['reward_type'] = 'pokemon';
                        $args['reward_id'] = $quest->pokemon_ids[0];
                    } elseif( !empty( $quest->reward_ids ) ) {
                        $args['reward_type'] = 'reward';
                        $args['reward_id'] = $quest->reward_ids[0];
                    }
                }
            }
        }

        if( isset($params['reward_type']) && $params['reward_type'] && isset($params['reward_id']) && $params['reward_id'] ) {
                $args['reward_type'] = $params['reward_type'];
                $args['reward_id'] = $params['reward_id'];
        }

        $instance = QuestInstance::create($args);

        if( $instance ) {
            $announce = Announce::create([
                'type' => 'quest-create',
                'source' => ( !empty($request->params['type']) ) ? $request->params['type'] : 'map',
                'date' => date('Y-m-d H:i:s'),
                'user_id' => Auth::id(),
                'quest_instance_id' => $instance->id,
            ]);
            $stop = Stop::find($args['gym_id']);
            $stop->touch();
            event( new \App\Events\QuestInstanceCreated( $instance, $announce ) );
            return $instance;
        }

        return false;

    }
}
