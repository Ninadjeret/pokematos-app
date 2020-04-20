<?php

namespace App\Models;

use App\Models\Guild;
use App\Models\Event;
use App\Models\EventQuiz;
use App\Models\EventTrain;
use App\Models\EventTrainStep;
use Illuminate\Support\Facades\Log;
use App\Events\Events\EventCreated;
use App\Events\Events\TrainCreated;
use App\Events\Events\TrainUpdated;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = ['city_id', 'guild_id', 'name', 'type', 'relation_id', 'start_time', 'end_time', 'discord_link', 'channel_discord_id', 'image'];
    protected $appends = ['relation', 'guild'];

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

    public function getImageAttribute( $value ) {
        if( empty($value) ) {
            return 'https://assets.profchen.fr/img/app/event_train_plain.jpg';
        }
        return $value;
    }

    public static function add($args) {

        $start_time = new \DateTime($args['event']['start_time']);
        $args['event']['end_time'] = $start_time->format('Y-m-d').' 23:59:00';
        if( !empty($args['event']['image']) && strstr($args['event']['image'], 'assets.profchen') ) unset($args['event']['image']);

        $event = Event::create( $args['event'] );

        //Event
        event(new EventCreated($event, $event->guild));

        if( $event->type == 'train' ) {

            $train = EventTrain::create([
                'event_id' => $event->id,
            ]);

            foreach( $args['steps'] as $arg ) {

                $step = EventTrainStep::create([
                    'train_id' => $train->id,
                ]);

                //Calcul de start_time
                $hour = $arg['hour'];
                if( strlen($hour) === 1 ) $hour = '0'.$hour;
                $minutes = $arg['minutes'];
                if( strlen($minutes) === 1 ) $minutes = '0'.$minutes;
                $event_start = new \DateTime( $event->start_time );
                $start_time = "{$event_start->format('Y-m-m')} {$hour}:{$minutes}:00";
                $arg['start_time'] = $start_time;
                if( array_key_exists('stop', $arg) && is_array($arg['stop']) && array_key_exists('id', $arg['stop']) ) {
                    $arg['stop_id'] = $arg['stop']['id'];
                } elseif( array_key_exists('stop', $arg) && is_object($arg['stop']) ) {
                    $arg['stop_id'] = $arg['stop']->id;
                }

                $step->change($arg);
            }

            //Event
            event(new TrainCreated($train, $event, $event->guild));

            return $event;
        } elseif( $event->type == 'quiz' ) {
            $event->setQuizz( $args );
        }
    }

    /**
     * Mise à jour d'un event
     *
     * @param array $args
     */
    public function change( $args ) {
        if( !empty($args['event']['image']) && strstr($args['event']['image'], 'assets.profchen') ) unset($args['event']['image']);
        $start_time = new \DateTime($args['event']['start_time']);
        $args['event']['end_time'] = $start_time->format('Y-m-d').' 23:59:00';
        $this->update($args['event']);

        if( $this->type == 'train' ) {

            $train = EventTrain::firstOrCreate(['event_id' => $this->id]);

            //On gère toutes les donnés dispos pour les steps
            $saved_steps = [];
            foreach( $args['steps'] as $args ) {

                //On crée ou on récupère l'étape
                if( !array_key_exists('id', $args) || empty($args['id']) ) {
                    $step = EventTrainStep::create([
                        'train_id' => $train->id,
                    ]);
                } else {
                    $step = EventTrainStep::find($args['id']);
                }

                //Calcul de start_time
                $hour = $args['hour'];
                if( strlen($hour) === 1 ) $hour = '0'.$hour;
                $minutes = $args['minutes'];
                if( strlen($minutes) === 1 ) $minutes = '0'.$minutes;
                $event_start = new \DateTime( $this->start_time );
                $start_time = "{$event_start->format('Y-m-m')} {$hour}:{$minutes}:00";
                $args['start_time'] = $start_time;
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

        } elseif( $this->type == 'quiz' ) {
            $this->setQuizz( $args );
        }

    }

    public function setQuizz( $args ) {

        if( empty($args['quiz']['difficulties']) ) $args['quiz']['difficulties'] = null;
        if( empty($args['quiz']['themes']) ) $args['quiz']['themes'] = null;

        $quiz = EventQuiz::firstOrCreate(['event_id' => $this->id]);
        $quiz->update($args['quiz']);

        $now = new \DateTime();
        $event_start = new \DateTime($this->start_time);
        if( $now < $event_start ) {
            $quiz->shuffleQuestions();
        }

    }

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
