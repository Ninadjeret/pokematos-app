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
    protected $appends = [ 'question', 'correctAnswer'];

    public function getQuestionAttribute() {
            return QuizQuestion::find($this->question_id);
    }

    public function getCorrectAnswerAttribute() {
        return EventQuizAnswer::where('question_id', $this->id)
            ->where('correct', 1)
            ->orderBy('answer_time', 'ASC')
            ->first();
    }

    public function getQuizAttribute() {
        return EventQuiz::find($this->quiz_id);
    }

    public function start() {

        if( !empty($this->quiz->event->channel_discord_id) ) {
            $this->quiz->sendToDiscord('Attention, question en approche...');
            sleep(10);
            $this->quiz->sendToDiscord("__:pencil: {$this->question->question}__");
        }

        $start_time = new \DateTime();
        $end_time = new \DateTime();
        $end_time->modify('+ '.$this->quiz->delay.' minutes');
        $this->update([
            'start_time' => $start_time->format('Y-m-d H:i:s'),
            'end_time' => $end_time->format('Y-m-d H:i:s'),
        ]);
    }

    public function isEnded() {
        $now = new \DateTime();
        $end_time = new \DateTime($this->end_time);
        return ( $now > $end_time );
    }

    public function addAnswer( $args ) {
        if( $this->isEnded() ) return false;

        $user = \App\User::where('discord_id', $args['user_discord_id'])->first();
        $guild = \App\Models\Guild::where('discord_id', $args['guild_discord_id'])->first();

        $answer = EventQuizAnswer::create([
            'answer' => $args['answer'],
            'question_id' => $this->id,
            'user_id' => $user->id,
            'guild_id' => $guild->id,
            'message_discord_id' => $args['message_discord_id'],
            'answer_time' => date('Y-m-d H:i:s'),
            'correct' => 0
        ]);

        if( $answer->isCorrect() ) {
            $this->close();
        }
    }

    public function close() {
        if( !empty( $this->correctAnswer ) ) {
            $this->quiz->sendToDiscord("Bravo @{$this->correctAnswer->user->name} :clap:");
        } else {
            $this->quiz->sendToDiscord("Dommage... La question devait être trop difficile :(");
        }
        $this->quiz->nextQuestion();
    }
}
