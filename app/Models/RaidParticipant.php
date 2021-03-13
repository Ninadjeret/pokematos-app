<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class RaidParticipant extends Model
{
    protected $fillable = ['raid_group_id', 'user_id', 'type', 'accounts'];

    public function getUserAttribute()
    {
        return User::find($this->user_id);
    }

    public function getTypeLabelAttribute()
    {
        if( $this->type == 'present' ) return 'sur place';
        if( $this->type == 'remote' ) return 'à distance';
        if( $this->type == 'invit' ) return 'avec invitation';
    }
}
