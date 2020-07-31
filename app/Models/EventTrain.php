<?php

namespace App\Models;

use App\Models\Event;
use App\Models\EventTrainStep;
use Illuminate\Database\Eloquent\Model;

class EventTrain extends Model
{
    protected $table = 'event_trains';
    protected $fillable = ['event_id', 'message_discord_id'];
    protected $appends = ['steps'];

    public function getEventAttribute()
    {
        return Event::find($this->event_id);
    }

    public function getStepsAttribute()
    {
        return EventTrainStep::where('train_id', $this->id)->orderBy('order', 'ASC')->get();
    }

    public function getNbStepsAttribute()
    {
        return count($this->steps);
    }

    public function getDiscordMessage()
    {

        $content = $this->getIntroMessage();
        $content .= $this->getWholeTrainMessage();

        if (strlen($content) > 1950) {
            $content = $this->getIntroMessage();
            $content .= $this->getPartialTrainMessage();
        }

        return $content;
    }

    private function getWholeTrainMessage()
    {
        $num = 0;
        $content = '';
        foreach ($this->steps as $step) {
            $num++;
            $content .= $this->getStepMessage($step, $num);
            if ($num < $this->nb_steps) $content .= "\t|\r\n";
        }
        return \App\Core\Discord::encode($content, $this->event->guild, false);
    }

    private function getPartialTrainMessage()
    {
        $steps_before = 3;
        $steps_after = 5;
        $nb_steps = $this->nb_steps;
        $current_step = $this->getCurrentStep()->order;

        $first_step = ($current_step - $steps_before > 0) ? $current_step - $steps_before : 1;
        $last_step = ($current_step + $steps_after <= $nb_steps) ? $current_step + $steps_after : $nb_steps;

        $hidden_before = ($first_step > 1) ? $first_step - 1 : 0;
        $hidden_after = ($last_step < $nb_steps) ? $nb_steps - $last_step : 0;

        $num = 0;
        $content = ($hidden_before > 0) ? "**{$hidden_before} étapes avant...**\r\n\t|\r\n" : "";
        foreach ($this->steps as $step) {
            $num++;
            if ($num >= $first_step && $num <= $last_step) {
                $content .= $this->getStepMessage($step, $num);
                if ($num < $this->nb_steps) $content .= "\t|\r\n";
            }
        }
        $content .= ($hidden_after > 0) ? "**{$hidden_after} étapes après...**" : "";
        return \App\Core\Discord::encode($content, $this->event->guild, false);
    }

    private function getIntroMessage()
    {
        $event = Event::find($this->event_id);
        $date = new \DateTime($event->start_time);
        $content = "Le parcours se déroule le {$date->format('d/m')} en {$this->nb_steps} étapes.:point_down:\r\n";
        $content .= "\t|\r\n";
        return $content;
    }

    private function getStepMessage($step, $num)
    {
        $name = '';
        $content = '';
        $steps = $this->steps;

        if ($step->type == 'stop' && $step->stop) {
            if ($step->stop->ex) $name .= '[EX] ';
            if ($step->stop->zone) $name .= $step->stop->zone->name . ' - ';
            $name .= $step->stop->name;
        } else {
            $name = 'Trajet en voiture/bus';
        }

        $emoji = ':white_circle:';
        $bold = '';
        $previous_step = ($num === 1) ? false : $steps[$num - 2];
        if (!$step->checked && $previous_step && $previous_step->checked) {
            $emoji = ':green_circle:';
            $bold = '**';
        } elseif (!$step->checked && !$previous_step) {
            $emoji = ':green_circle:';
            $bold = '**';
        } elseif (!$step->checked) {
            $emoji = ':radio_button:';
            $bold = '';
        }

        $time_str = '';
        if ($step->milestone) {
            $time = new \DateTime($step->start_time);
            $time_str = "{$time->format('H\hi')} : ";
        }

        $content .= "{$emoji} {$bold}{$time_str}{$name}\r\n{$bold}";
        if (!empty($step->description)) $content .= "\t|\t{$step->description}\r\n";
        return $content;
    }

    public function getCurrentStep()
    {
        $step = EventTrainStep::where('train_id', $this->id)
            ->where('checked', 0)
            ->orderBy('order', 'ASC')
            ->first();
        return (!empty($step)) ? $step : false;
    }
}