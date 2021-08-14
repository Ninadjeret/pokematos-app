<?php

namespace App\Models;

use App\Models\EventQuiz;
use App\Models\QuizQuestion;
use App\Models\EventQuizAnswer;
use App\Core\Events\Quizs\Ranking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class EventQuizQuestion extends Model
{
    protected $table = 'event_quiz_questions';
    protected $fillable = ['quiz_id', 'question_id', 'start_time', 'end_time', 'order'];
    protected $appends = ['question', 'correctAnswer'];

    public function getQuestionAttribute()
    {
        return QuizQuestion::find($this->question_id);
    }

    public function getCorrectAnswerAttribute()
    {
        return EventQuizAnswer::where('question_id', $this->id)
            ->where('correct', 1)
            ->orderBy('answer_time', 'ASC')
            ->first();
    }

    public function getQuizAttribute()
    {
        return EventQuiz::find($this->quiz_id);
    }

    public function start()
    {

        //On empeche de déclencher deux fois la question
        if (!empty($this->start_time)) return;

        $start_time = new \DateTime();
        $this->update([
            'start_time' => $start_time->format('Y-m-d H:i:s'),
        ]);

        sleep(20);

        $start_time = new \DateTime();
        $end_time = new \DateTime();
        $end_time->modify('+ ' . $this->quiz->delay . ' minutes');

        $this->update([
            'start_time' => $start_time->format('Y-m-d H:i:s'),
            'end_time' => $end_time->format('Y-m-d H:i:s'),
        ]);

        $this->quiz->sendToDiscord('question_question', [
            '%question' => $this->question->question
        ], null, array(
            'thumbnail' => EventQuiz::getEmbedThumbnails()->question,
            'footer' => ['text' => "Question {$this->order}/{$this->quiz->getNbQuestions()} - {$this->question->difficulty} points - Thème {$this->question->theme->name}"]
        ));
    }

    public function isEnded()
    {
        $now = new \DateTime();
        $end_time = new \DateTime($this->end_time);
        return ($now > $end_time);
    }

    public function addAnswer($args)
    {
        if ($this->isEnded()) return false;

        $user = \App\User::find($args['user_id']);
        $guild = \App\Models\Guild::where('discord_id', $args['guild_discord_id'])->first();

        //Quiz managers, as they can edit all questions, are not able to play quizs
        if ($user->can('quiz_manage') && !$user->superadmin) {
            return;
        }

        $answer = EventQuizAnswer::create([
            'answer' => $args['answer'],
            'question_id' => $this->id,
            'user_id' => $user->id,
            'guild_id' => $guild->id,
            'message_discord_id' => $args['message_discord_id'],
            'answer_time' => date('Y-m-d H:i:s'),
            'correct' => 0
        ]);

        if ($this->isAlreadyAnswered()) {
            $this->quiz->sendToDiscord('question_already_answered', ['%user' => $user], $guild);
        } elseif ($answer->isCorrect()) {
            $this->close();
        } else {
            $rand = rand(1, 4);
            if ($rand === 1) {
                $this->quiz->sendToDiscord('question_answer_wrong', ['%user' => $user], $guild);
            }
        }
    }

    public function close()
    {
        $end_time = new \DateTime();
        $this->update(['end_time' => $end_time->format('Y-m-d H:i:s')]);

        if (!empty($this->correctAnswer)) {
            if ($this->quiz->event->multi_guilds) {
                $this->quiz->sendToDiscord('question_answer_correct', [
                    '%user' => $this->correctAnswer->user,
                    '%answer' => $this->question->answer,
                ], $this->correctAnswer->guild);
                foreach ($this->getUncorrectGuilds() as $guild) {
                    $this->quiz->sendToDiscord('question_answer_correct_by_another', [
                        '%answer' => $this->question->answer,
                        '%guild' => $this->correctAnswer->guild->name,
                    ], $guild);
                }
                $ranking = new Ranking($this->quiz->questions);
                $this->quiz->sendToDiscord('question_multi_ranking', ['%ranking' => $ranking->formatMultiRanking()]);
            } else {
                $this->quiz->sendToDiscord('question_answer_correct', [
                    '%user' => $this->correctAnswer->user,
                    '%answer' => $this->question->answer,
                ]);
            }
            if (!empty($this->question->explanation)) {
                $this->quiz->sendToDiscord('question_answer_explanation', [
                    '%answer' => $this->question->answer,
                    '%explanation' => $this->question->explanation
                ]);
            }
        } else {
            $this->quiz->sendToDiscord('question_not_answered');
        }
        $this->quiz->nextQuestion();
    }

    public function isAlreadyAnswered()
    {
        $answer = EventQuizAnswer::where('question_id', $this->id)
            ->where('correct', 1)
            ->orderBy('answer_time', 'ASC')
            ->first();
        return (empty($answer)) ? false : true;
    }

    public function getUncorrectGuilds()
    {
        $guilds = [];
        $correct_id = $this->correctAnswer->guild_id;
        foreach ($this->quiz->event->guilds as $guild) {
            if ($guild->id == $correct_id) continue;
            $guilds[] = $guild;
        }
        return $guilds;
    }
}