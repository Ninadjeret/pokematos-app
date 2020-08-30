<?php

namespace App\Models;

use App\Models\Stop;
use App\Models\Event;
use App\Models\EventTrain;
use App\Events\Events\TrainUpdated;
use Illuminate\Support\Facades\Log;
use App\Core\Discord\MessageTranslator;
use App\Events\Events\TrainStepChecked;
use Illuminate\Database\Eloquent\Model;
use App\Events\Events\TrainStepUnchecked;

class EventTrainStep extends Model
{
    protected $table = 'event_train_steps';
    protected $fillable = ['train_id', 'type', 'stop_id', 'milestone', 'order', 'start_time', 'duration', 'description', 'checked', 'message_discord_id'];
    protected $appends = ['stop', 'hour', 'minutes', 'key', 'opened'];

    public function getStopAttribute()
    {
        if (empty($this->stop_id)) return false;
        return Stop::find($this->stop_id);
    }

    public function getKeyAttribute()
    {
        return 'key-' . $this->id;
    }

    public function getOpenedAttribute()
    {
        return false;
    }

    public function getHourAttribute()
    {
        $date = new \DateTime($this->start_time);
        return $date->format('G');
    }

    public function getMinutesAttribute()
    {
        $date = new \DateTime($this->start_time);
        return intval(ltrim($date->format('i'), '0'));
    }

    public function getTrainAttribute()
    {
        return EventTrain::find($this->train_id);
    }

    public function getNameAttribute()
    {
        $name = '';
        if ($this->type == 'stop' && $this->stop) {
            if ($this->stop->zone) $name .= $this->stop->zone->name . ' - ';
            $name .= $this->stop->name;
        } else {
            $name = 'Trajet en voiture/bus';
        }
        return $name;
    }

    public function change($args)
    {
        $this->update($args);
    }

    public function isLast()
    {
        $last = $this->train->steps->last();
        if ($last['id'] == $this->id) return true;
        return false;
    }

    public function getPreviousStep()
    {
        $order = $this->order;
        $order--;
        $previous = EventTrainStep::where('train_id', $this->train_id)->where('order', $order)->first();
        if (empty($previous)) return false;
        return $previous;
    }

    public function getNextStep()
    {
        $order = $this->order;
        $order++;
        $next = EventTrainStep::where('train_id', $this->train_id)->where('order', $order)->first();
        if (empty($next)) return false;
        return $next;
    }

    public function check()
    {
        $this->update(['checked' => 1]);

        //Event
        $train = EventTrain::find($this->train_id);
        $event = Event::find($train->event_id);
        event(new TrainUpdated($train, $event, $event->guild));
        event(new TrainStepChecked($this, $train, $event, $event->guild));

        return true;
    }

    public function uncheck()
    {
        $this->update(['checked' => 0]);
        //Event
        $train = EventTrain::find($this->train_id);
        $event = Event::find($train->event_id);
        event(new TrainUpdated($train, $event, $event->guild));
        event(new TrainStepUnchecked($this, $train, $event, $event->guild));

        return true;
    }

    public function getDiscordMessage()
    {
        if ($this->isLast()) return '';

        $event = Event::find($this->train->event_id);

        $start_time = ($this->milestone) ? new \DateTime($this->start_time) : false;
        $next_step = $this->getNextStep();
        $next_start_time = ($next_step->milestone) ? new \DateTime($next_step->start_time) : false;

        $translatable = [
            'etape_nom' => $this->name,
            'etape_heure' => ($start_time) ? $start_time->format('H\hi') : '',
            'etape_description' => $this->description,
            'next_etape_nom' => $next_step->name,
            'next_etape_heure' => ($next_start_time) ? $next_start_time->format('H\hi') : '',
            'next_etape_description' => $next_step->description
        ];

        return MessageTranslator::to($event->guild)
            ->addCustomTranslatable($translatable)
            ->translate($event->guild->settings->events_trains_message_check);
    }
}