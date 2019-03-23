<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use App\models\Guild;

class Role extends Model {

    protected $fillable = ['discord_id', 'guild_id', 'name', 'type', 'relation_id', 'restricted'];
    protected $appends = ['guild'];
    protected $hidden = ['guild_id'];
    protected $casts = [
        'restricted' => 'boolean'
    ];

    public function getGuildAttribute() {
        return Guild::find($this->guild_id);
    }

}
