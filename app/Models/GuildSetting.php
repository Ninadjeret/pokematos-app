<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuildSetting extends Model {

    protected $table = 'guild_settings';
    protected $fillable = ['guild_id', 'key', 'value'];

}
