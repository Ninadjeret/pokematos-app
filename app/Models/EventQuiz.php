<?php

namespace App\Models;

use App\Models\QuizQuestion;
use App\Models\QuizTheme;
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

    public function shuffleQuestions() {
        EventQuizQuestion::where('quiz_id', $this->id)->delete();

        $difficulties = ( empty($this->difficulties) ) ? [1, 2, 3] : $this->difficulties ;
        $themes = ( empty($this->themes) ) ? \DB::table('quiz_themes')->pluck('id') : $this->themes ;

        $questions = QuizQuestion::where('about_pogo', $this->only_pogo)
            ->whereIn('difficulty', $difficulties)
            ->whereIn('theme_id', $themes)
            ->inRandomOrder()
            ->take($this->nb_questions)
            ->get();
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
