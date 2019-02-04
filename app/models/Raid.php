<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Stop;

class Raid extends Model {

    protected $hidden = ['gym_id', 'city_id'];
    protected $appends = ['gym'];

    public function getGymAttribute() {
        return Stop::find($this->gym_id);
    }

}
