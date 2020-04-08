<?php

namespace App\Models;

use App\Models\Stop;
use Illuminate\Database\Eloquent\Model;

class EventTrainStep extends Model
{
    protected $table = 'event_train_steps';
    protected $fillable = ['train_id', 'type', 'stop_id', 'start_time', 'duration', 'description', 'checked'];
    protected $appends = ['stop'];

    public function getStopAttribute() {
        if( empty($this->stop_id) ) return false;
        return Stop::find($this->stop_id);
    }
}
