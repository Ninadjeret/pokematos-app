<?php

namespace App\Models;

use App\Models\EventTrainStep;
use Illuminate\Database\Eloquent\Model;

class EventTrain extends Model
{
    protected $table = 'event_trains';
    protected $fillable = ['event_id', 'message_discord_id'];
    protected $appends = ['steps'];

    public function getStepsAttribute() {
        return EventTrainSteps::where('train_id', $this->id)->get();
    }
}
