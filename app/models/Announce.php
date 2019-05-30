<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Announce extends Model {

    protected $fillable = [ 'raid_id', 'source', 'type', 'url', 'content', 'date', 'message_id', 'user_id', 'guild_id',];

    public function getUser() {
        return User::find( $this->user_id );
    }

}
