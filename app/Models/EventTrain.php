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

    public function getStepsAttribute() {
        return EventTrainStep::where('train_id', $this->id)->orderBy('start_time', 'ASC')->get();
    }

    public function getDiscordMessage() {

        $steps = $this->steps;
        $count = count($steps);
        $event = Event::find($this->event_id);

        $date = new \DateTime( $event->start_time );

        $content = "Le parcours se déroule le {$date->format('d/m')} en {$count} étapes.:point_down:\r\n";
        $content .= "\t|\r\n";

        $num = 0;
        foreach( $steps as $step ) {
            $num++;

            $name = '';
            if( $step->type == 'stop' && $step->stop ) {
                if( $step->stop->ex ) $name .= '[EX] ';
                if($step->stop->zone ) $name .= $step->stop->zone->name.' - ';
                $name .= $step->stop->name;
            } else {
                $name = 'Trajet en voiture/bus';
            }
            $time = new \DateTime($step->start_time);

            $emoji = ':white_circle:';
            $bold = '';
            $previous_step = ($num === 1) ? false : $steps[$num-2] ;
            if( !$step->checked && $previous_step && $previous_step->checked ) {
                $emoji = ':green_circle:';
                $bold = '**';
            } elseif( !$step->checked && !$previous_step) {
                $emoji = ':green_circle:';
                $bold = '**';
            } elseif( !$step->checked ) {
                $emoji = ':radio_button:';
                $bold = '';
            }

            $content .= "{$emoji} {$bold}{$time->format('H\hi')} : {$name}\r\n{$bold}";
            if( !empty($step->description) ) $content .= "   |   {$step->description}\r\n";

            if( $num < $count ) $content .= "\t|\r\n";
        }

        return \App\Core\Discord::encode($content, $event->guild, false);
    }
}
