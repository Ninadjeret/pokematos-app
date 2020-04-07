<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventTrainStep extends Model
{
    protected $table = 'event_train_steps';
    protected $fillable = ['train_id', 'type', 'stop_id', 'start_time', 'duration', 'description', 'checked'];
}
