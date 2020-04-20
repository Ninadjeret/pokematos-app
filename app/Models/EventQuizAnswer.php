<?php

namespace App\Models;

use App\User;
use App\Models\Guild;
use Illuminate\Database\Eloquent\Model;

class EventQuizAnswer extends Model
{
    protected $table = 'event_quiz_answers';
    protected $fillable = ['question_id', 'user_id', 'guild_id', 'message_discord_id', 'answer_time', 'correct'];

    public function getUserAttribute() {
        return User::find($this->user_id);
    }

    public function getGuildAttribute() {
        return Guild::find($this->guild_id);
    }

}
