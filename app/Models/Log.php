<?php

namespace App\Models;

use App\User;
use App\Models\Guild;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $fillable = [
        'city_id',
        'guild_id',
        'type',
        'success',
        'error',
        'source_type',
        'source',
        'result',
        'user_id',
        'channel_discord_id',
    ];
    protected $appends = [
        'guild', 'user',
    ];
    protected $hidden = ['city_id', 'guild_id', 'user_id'];
    protected $casts = [
        'result' => 'array',
        'success' => 'boolean',
    ];

    public function getGuildAttribute() {
        if( empty( $this->guild_id ) ) return false;
        return Guild::find($this->guild_id);
    }

    public function getUserAttribute() {
        if( empty( $this->user_id ) ) return false;
        return User::find($this->user_id);
    }
}
