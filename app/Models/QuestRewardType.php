<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestRewardType extends Model
{
    protected $table = 'quest_reward_types';
    protected $fillable = ['slug', 'niantic_id', 'name'];
}
