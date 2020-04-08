<?php

namespace App\Models;

use App\Models\Event;
use App\Models\EventTrain;
use App\Models\EventTrainStep;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = ['city_id', 'guid_id', 'name', 'type', 'relation_id', 'start_time', 'end_time', 'discord_link', 'channel_discord_id'];
    protected $appends = ['relation'];

    public function getRelationAttribute() {
        if( $this->type == 'train' ) {
            $train = EventTrain::find($this->relation_id);
            if( $train ) {
                return $train;
            }
        }
        return false;
    }

    public static function add($args) {
        $event = Event::create( $args['event'] );

        if( array_key_exists('steps', $args) ) {

            $train = EventTrain::create([
                'event_id' => $event->id,
            ]);

            foreach( $args['steps'] as $step ) {
                EventTrainStep::create([
                    'train_id' => $train->id,
                    'type' => $step->type,
                    'stop_id' => $step->stop_id,
                    'start_time' => $step->start_time,
                    'duration' => $step->duration,
                    'description' => $step->description,
                    'checked' => 0,
                ]);
            }

        }
    }
}
