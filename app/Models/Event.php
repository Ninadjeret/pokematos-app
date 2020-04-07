<?php

namespace App\Models;

use App\Models\EventTrain;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = ['name', 'type', 'relation_id', 'start_time', 'end_time', 'channel_discord_id'];
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
}
