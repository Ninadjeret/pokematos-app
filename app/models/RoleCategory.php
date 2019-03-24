<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class RoleCategory extends Model {

    protected $fillable = ['guild_id', 'name', 'restricted', 'channel_discord_id'];
    protected $appends = ['guild'];
    protected $hidden = ['guild_id'];
    protected $casts = [
        'restricted' => 'boolean'
    ];

    public function getGuildAttribute() {
        return Guild::find($this->guild_id);
    }

}
