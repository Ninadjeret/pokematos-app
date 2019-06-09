<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Quest extends Model
{
    protected $fillable = ['mission_id', 'reward_id', 'pokemon_id'];
}
