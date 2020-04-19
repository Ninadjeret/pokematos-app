<?php

namespace App\Models;

use App\Models\EventQuiz;
use App\Models\QuizQuestion;
use App\Models\EventQuizQuestion;
use Illuminate\Database\Eloquent\Model;

class EventQuizQuestion extends Model
{
    protected $table = 'event_quiz_questions';
    protected $fillable = ['quiz_id', 'question_id', 'start_time', 'end_time', 'order'];
    protected $appends = [ 'question', 'answer'];

    public function getQuestionAttribute() {
            return QuizQuestion::find($this->question_id);
    }

    public function getAnswerAttribute() {
        return EventQuizAnswer::where('question_id', $this->id)->orderBy('answer_time', 'ASC')->first();
    }

    public function getQuizAttribute() {
        return EventQuiz::find($this->quiz_id);
    }
}
