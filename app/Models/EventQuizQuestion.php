<?php

namespace App\Models;

use App\Models\QuizQuestion;
use App\Models\EventQuizQuestion;
use Illuminate\Database\Eloquent\Model;

class EventQuizQuestion extends Model
{
    protected $table = 'event_quiz_questions';
    protected $fillable = ['quiz_id', 'question_id', 'start_time', 'end_time'];
    protected $appends = [ 'question', 'answers'];

    public function getQuestionAttribute() {
            return QuizQuestion::find($this->question_id);
    }

    public function getAnswersAttribute() {
        return EventQuizAnswer::where('question_id', $this->id)->orderBy('answer_time', 'ASC')->get();
    }
}
