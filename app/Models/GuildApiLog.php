<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuildApiLog extends Model
{
    protected $table = 'guild_api_logs';
    protected $fillable = ['api_access_id', 'endpoint', 'status'];
}