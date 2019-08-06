<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserGuild extends Model {

    protected $table = 'user_guilds';
    protected $fillable = ['guild_id', 'user_id', 'user_roles', 'permissions'];
    protected $casts = ['admin' => 'boolean'];

}
