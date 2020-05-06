<?php

namespace App\Models;

use App\Models\Stop;
use App\Models\Event;
Use App\Models\EventTrain;
use App\Events\Events\TrainUpdated;
use Illuminate\Database\Eloquent\Model;
use App\Events\Events\TrainStepChecked;
use App\Events\Events\TrainStepUnchecked;
use Illuminate\Support\Facades\Log;

class EventTrainStep extends Model
{
    protected $table = 'event_train_steps';
    protected $fillable = ['train_id', 'type', 'stop_id', 'milestone', 'order', 'start_time', 'duration', 'description', 'checked', 'message_discord_id'];
    protected $appends = ['stop', 'hour', 'minutes', 'key', 'opened'];

    public function getStopAttribute() {
        if( empty($this->stop_id) ) return false;
        return Stop::find($this->stop_id);
    }

    public function getKeyAttribute() {
        return 'key-'.$this->id;
    }

    public function getOpenedAttribute() {
        return false;
    }

    public function getHourAttribute() {
        $date = new \DateTime($this->start_time);
        return $date->format('G');
    }

    public function getMinutesAttribute() {
        $date = new \DateTime($this->start_time);
        return intval(ltrim($date->format('i'), '0'));
    }

    public function getTrainAttribute() {
        return EventTrain::find($this->train_id);
    }

    public function getNameAttribute() {
        $name = '';
        if( $this->type == 'stop' && $this->stop ) {
            if($this->stop->zone ) $name .= $this->stop->zone->name.' - ';
            $name .= $this->stop->name;
        } else {
            $name = 'Trajet en voiture/bus';
        }
        return $name;
    }

    public function change( $args ) {
        $this->update($args);
    }

    public function isLast() {
        $last = $this->train->steps->last();
        if( $last['id'] == $this->id ) return true;
        return false;
    }

    public function getPreviousStep() {
        $steps = $this->train->steps;
        $current = false;
        $num = 0;
        foreach( $steps as $step ) {
            if( $step->id == $this->id ) {
                $current = $num;
            }
            $num++;
        }
        return $steps[$num-1];
    }

    public function check() {
        $this->update(['checked' => 1]);

        //Event
        $train = EventTrain::find($this->train_id);
        $event = Event::find($train->event_id);
        event(new TrainUpdated($train, $event, $event->guild));
        event(new TrainStepChecked($this, $train, $event, $event->guild));

        return true;
    }

    public function uncheck() {
        $this->update(['checked' => 0]);

        //Event
        $train = EventTrain::find($this->train_id);
        $event = Event::find($train->event_id);
        event(new TrainUpdated($train, $event, $event->guild));
        event(new TrainStepUnchecked($this, $train, $event, $event->guild));

        return true;
    }

    public function getDiscordMessage() {

        if($this->isLast()) return '';

        $event = Event::find($this->train->event_id);

        $start_time = new \DateTime($this->start_time);
        $prev_step = $this->getPreviousStep();
        $prev_start_time = new \DateTime($prev_step->start_time);

        $content = str_replace([
            '{etape_nom}',
            '{etape_heure}',
            '{etape_description}',
            '{next_etape_nom}',
            '{next_etape_heure}',
            '{next_etape_description}'
        ], [
            $this->name,
            $start_time->format('H\hi'),
            $this->description,
            $prev_step->name,
            $prev_start_time->format('H\hi'),
            $prev_step->description
        ],$event->guild->settings->events_trains_message_check);

        return \App\Core\Discord::encode($content, $event->guild, false);
    }
}
