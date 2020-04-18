<?php

namespace App\Models;

use App\Models\QuizQuestion;
use App\Models\EventQuizQuestion;
use Illuminate\Database\Eloquent\Model;

class EventQuiz extends Model
{
    protected $table = 'event_quizs';
    protected $fillable = ['event_id', 'nb_questions', 'delay', 'themes', 'difficulties', 'only_pogo', 'message_discord_id'];
    protected $appends = ['questions'];

    public function getQuestionsAttribute() {
        return EventQuizQuestion::where('quiz_id', $this->id)->orderBy('start_time', 'ASC')->get();
    }

    public function shuffleQuestions($number, $delay, $themes, $only_pogo, $difficulties) {
        $questions = QuizQuestion::where('about_pogo', $only_pogo)
            ->whereIn('difficulty', $difficulties)
            ->whereIn('theme_id', $themes)
            ->get($number);
        if( !empty($questions) ) {
            $order = 0;
            foreach( $questions as $question ) {
                $order++;
                EventQuizQuestion::create([
                    'quiz_id' => $this->id,
                    'question_id' => $question->id,
                    'order' => $order,
                ]);
            }
        }
    }
}
