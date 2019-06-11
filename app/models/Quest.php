<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Quest extends Model
{
    protected $fillable = ['name', 'reward_type', 'reward_id', 'pokemon_id'];
}
