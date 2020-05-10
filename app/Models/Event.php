<?php

namespace App\Models;

use App\Models\Guild;
use App\Models\Event;
use App\Models\EventQuiz;
use App\Models\EventTrain;
use App\Models\EventInvit;
use App\Models\EventTrainStep;
use Illuminate\Support\Facades\Log;
use App\Events\Events\EventCreated;
use App\Events\Events\TrainCreated;
use App\Events\Events\TrainUpdated;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = ['city_id', 'guild_id', 'name', 'type', 'relation_id', 'start_time', 'end_time', 'discord_link', 'channel_discord_id', 'image', 'multi_guilds'];
    protected $appends = ['relation', 'guild', 'guests'];

    public static $multi_types = ['quiz'];

    public function getRelationAttribute() {
        if( $this->type == 'train' ) {
            $train = EventTrain::where('event_id', $this->id)->first();
            if( $train ) {
                return $train;
            }
        } elseif( $this->type == 'quiz' ) {
            return $this->quiz;
        }
        return false;
    }

    public function getGuestsAttribute() {
        if( $this->multi_guilds ) {
            return EventInvit::where('event_id', $this->id)->get();
        }
        return [];
    }

    public function getQuizAttribute() {
        $quiz = EventQuiz::where('event_id', $this->id)->first();
        if( $quiz ) {
            return $quiz;
        }
        return false;
    }

    public function getGuildAttribute() {
        return Guild::find($this->guild_id);
    }

    public function getGuildsAttribute() {
        $guilds = [];
        $guilds[] = Guild::find($this->guild_id);
        if( $this->multi_guilds ) {
            foreach( $this->guests as $guest ) {
                if( $guest->status != 'accepted' ) continue;
                $guilds[] = Guild::find($guest->guild_id);
            }
        }
        return $guilds;
    }

    public function getImageAttribute( $value ) {
        if( empty($value) ) {
            return 'https://assets.profchen.fr/img/app/event_train_plain.jpg';
        }
        return $value;
    }

    public function invits() {
        return $this->hasMany('App\Models\EventInvit');
    }

    public static function add($args) {

        $args = Event::formatArgs($args);
        $event = Event::create( $args['event'] );
        event(new EventCreated($event, $event->guild));

        if( $event->type == 'train' ) {
            $event->setTrain( $args );
            $event->resetQuizz();
        } elseif( $event->type == 'quiz' ) {
            $event->setQuizz( $args );
            $event->resetTrain();
        }

        $event->setMultiQuilds($args);

        return $event;
    }

    /**
     * Mise à jour d'un event
     *
     * @param array $args
     */
    public function change( $args ) {

        $args = Event::formatArgs($args);
        $this->update($args['event']);

        if( $this->type == 'train' ) {
            $this->setTrain( $args );
            $this->resetQuizz();
        } elseif( $this->type == 'quiz' ) {
            $this->setQuizz( $args );
            $this->resetTrain();
        }

        $this->setMultiQuilds($args);

    }

    public function suppr() {
        $this->CancelInvits();
        $this->resetTrain();
        $this->resetQuizz();
        $this->delete();
        return true;
    }

    public static function formatArgs($args) {
        if( !empty($args['event']['image']) && strstr($args['event']['image'], 'assets.profchen') ) unset($args['event']['image']);
        $start_time = new \DateTime($args['event']['start_time']);
        $args['event']['end_time'] = $start_time->format('Y-m-d').' 23:59:00';
        if( empty( $args['event']['multi_guilds'] ) ) $args['event']['multi_guilds'] = 0;
        return $args;
    }


    public function setTrain( $args2 ) {
        $train = EventTrain::firstOrCreate(['event_id' => $this->id]);

        //On gère toutes les donnés dispos pour les steps
        $saved_steps = [];
        $order = 0;
        foreach( $args2['steps'] as $args ) {
            $order++;
            $args['order'] = $order;

            //On crée ou on récupère l'étape
            if( !array_key_exists('id', $args) || empty($args['id']) ) {
                $step = EventTrainStep::create([
                    'train_id' => $train->id,
                    'order' => $order,
                ]);
            } else {
                $step = EventTrainStep::find($args['id']);
            }

            //Calcul de start_time
            $args['start_time'] = null;
            if( isset($args['milestone']) && $args['milestone'] == 1 ) {
                $hour = $args['hour'];
                if( strlen($hour) === 1 ) $hour = '0'.$hour;
                $minutes = $args['minutes'];
                if( strlen($minutes) === 1 ) $minutes = '0'.$minutes;
                $event_start = new \DateTime( $this->start_time );
                $start_time = "{$event_start->format('Y-m-m')} {$hour}:{$minutes}:00";
                $args['start_time'] = $start_time;
            }

            if( array_key_exists('stop', $args) && is_array($args['stop']) && array_key_exists('id', $args['stop']) ) {
                $args['stop_id'] = $args['stop']['id'];
            } elseif( array_key_exists('stop', $args) && is_object($args['stop']) ) {
                $args['stop_id'] = $args['stop']->id;
            }

            $step->change($args);
            $saved_steps[] = $step->id;
        }

        //On supprime les anciennes steps
        EventTrainStep::where('train_id', $train->id)->whereNotIn('id', $saved_steps)->delete();

        //Event
        event(new TrainUpdated($train, $this, $this->guild));
    }


    /**
     * [setMultiQuilds description]
     * @param [type] $args [description]
     */
    public function setMultiQuilds( $args ) {
        if( in_array($this->type, self::$multi_types) ) {
            if( $this->multi_guilds  ) {
                $this->manageInvits($args['guests']);
            } else {
                $this->cancelInvits();
            }
        } else {
            $this->update(['multi_guilds' => false]);
            $this->CancelInvits();
        }
    }


    /**
     * [manageInvits description]
     * @param  [type] $guests [description]
     * @return [type]         [description]
     */
    public function manageInvits($guests) {

        if( empty($guests) ) {
                $this->CancelInvits();
                return;
        }

        $invit_ids = [];
        foreach( $guests as $guest ) {
            $invit = EventInvit::where('event_id', $this->id)
                ->where('guild_id', $guest['guild_id'])
                ->first();
            if( empty($invit) ) {
                $invit = EventInvit::add([
                    'event_id' => $this->id,
                    'guild_id' => $guest['guild_id']
                ]);
            }
            $invit_ids[] = $invit->id;
        }

        if( !empty($invit_ids) ) {
            $this->CancelInvits($not_in = $invit_ids);
        }

    }

    /**
     * [CancelInvits description]
     * @param [type] $not_in [description]
     */
    public function CancelInvits( $not_in = null ) {
        $query = EventInvit::where('event_id', $this->id);
        if( !empty($not_in) ) $query->whereNotIn('id', $not_in);
        $invits = $query->get();
        if( $invits->isEmpty() ) {
            return;
        }
        foreach( $invits as $invit ) {
            $invit->cancel();
        }
    }


    /**
     * [setQuizz description]
     * @param [type] $args [description]
     */
    public function setQuizz( $args ) {
        if( empty($args['quiz']['difficulties']) ) $args['quiz']['difficulties'] = null;
        if( empty($args['quiz']['themes']) ) $args['quiz']['themes'] = null;
        if( empty($args['quiz']['status']) ) $args['quiz']['status'] = 'future';

        $quiz = EventQuiz::firstOrCreate(['event_id' => $this->id]);
        $quiz->update($args['quiz']);

        if( $quiz->status == 'future' ) {
            $quiz->shuffleQuestions();
        }

    }

    public function resetTrain() {
        $train = EventTrain::where('event_id', $this->id)->first();
        if( empty($train) ) {
            return;
        }
        EventTrainStep::where('train_id', $train->id)->delete();
        EventTrain::destroy($train->id);
        return true;
    }

    public function resetQuizz() {
        $quiz = EventQuiz::where('event_id', $this->id)->first();
        if( empty($quiz) ) {
            return;
        }
        EventQuizQuestion::where('quiz_id', $quiz->id)->delete();
        EventQuiz::destroy($quiz->id);
        return true;
    }


    /**
     * [findFromChannelId description]
     * @param  [type] $channel_idscord_id [description]
     * @return [type]                     [description]
     */
    public static function findFromChannelId( $channel_idscord_id ) {
        $event = Event::where('channel_discord_id', $channel_idscord_id)->first();
        if( !empty($event) ) return $event;
        $invit = EventInvit::where('channel_discord_id', $channel_idscord_id)->first();
        if( !empty($invit) ) return Event::find($invit->event_id);
    }


    /**
     * [getActiveEvents description]
     * @param  [type] $type [description]
     * @return [type]       [description]
     */
    public static function getActiveEvents( $type = null ) {

        $now = new \DateTime();
        $yesterday = clone $now;
        $yesterday->modify('- 1  day');

        if( empty($type) ) {
            return Event::where('start_time', '<', $now->format('Y-m-d H:i:s'))
                ->where('end_time', '>', $now->format('Y-m-d H:i:s'))
                ->get();
        } elseif( is_array($type) ) {
            return Event::where('start_time', '<', $now->format('Y-m-d H:i:s'))
                ->where('end_time', '>', $now->format('Y-m-d H:i:s'))
                ->whereIn('type', $type)
                ->get();
        } else {
            return Event::where('start_time', '<', $now->format('Y-m-d H:i:s'))
                ->where('end_time', '>', $now->format('Y-m-d H:i:s'))
                ->where('type', $type)
                ->get();
        }
    }
}
