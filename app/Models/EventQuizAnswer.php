<?php

namespace App\Models;

use App\User;
use App\Models\Guild;
use App\Models\EventQuizQuestion;
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
        if (empty($this->question->alt_answers) && is_array($this->question->alt_answers)) {
            foreach ($this->question->alt_answers as $answer) {
                $answers[] = \App\Core\Helpers::sanitize($answer);
            }
        }

        //Check for correct answer
        foreach ($answers as $answer) {
            if (\App\Core\Helpers::sanitize($this->answer) == $answer) {
                $this->update(['correct' => 1]);
                return true;
            }
        }
        return false;
    }
}