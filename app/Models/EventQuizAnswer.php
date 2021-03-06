<?php

namespace App\Models;

use App\User;
use App\Models\Guild;
use App\Models\EventQuizQuestion;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;

class EventQuizAnswer extends Model
{
    protected $table = 'event_quiz_answers';
    protected $fillable = ['answer', 'question_id', 'user_id', 'guild_id', 'message_discord_id', 'answer_time', 'correct'];

    public function getUserAttribute()
    {
        return User::find($this->user_id);
    }

    public function getGuildAttribute()
    {
        return Guild::find($this->guild_id);
    }

    public function getQuestionAttribute()
    {
        return EventQuizQuestion::find($this->question_id);
    }

    public function isCorrect()
    {
        //Agregration et sanitize for all answers
        $answers = [\App\Core\Helpers::sanitize($this->question->question->answer)];
        $alt_answers = $this->question->question->alt_answers;
        if (!empty($alt_answers) && is_array($alt_answers)) {
            foreach ($alt_answers as $answer) {
                $answers[] = \App\Core\Helpers::sanitize($answer);
            }
        }

        //Check for correct answer
        foreach ($answers as $answer) {
            $toto = \App\Core\Helpers::sanitize($this->answer);
            if (strstr(\App\Core\Helpers::sanitize($this->answer), $answer)) {
                $this->update(['correct' => 1]);
                return true;
            }
        }
        return false;
    }
}