<?php

namespace App\Models;

use App\Models\Stop;
use Illuminate\Database\Eloquent\Model;

class EventTrainStep extends Model
{
    protected $table = 'event_train_steps';
    protected $fillable = ['train_id', 'type', 'stop_id', 'start_time', 'duration', 'description', 'checked'];
    protected $appends = ['stop', 'hour', 'minutes'];

    public function getStopAttribute() {
        if( empty($this->stop_id) ) return false;
        return Stop::find($this->stop_id);
    }

    public function getHourAttribute() {
        $date = new \DateTime($this->start_time);
        return $date->format('G');
    }

    public function getMinutesAttribute() {
        $date = new \DateTime($this->start_time);
        return intval(ltrim($date->format('i'), '0'));
    }

    public function change( $args ) {
        $this->update($args);
    }

    public function check() {
        $this->update(['checked' => 1]);
        return true;
    }

    public function uncheck() {
        $this->update(['checked' => 0]);
        return true;
    }
}
