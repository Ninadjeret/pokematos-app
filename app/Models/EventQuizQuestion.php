<?php

namespace App\Models;

use App\Models\EventQuiz;
use App\Models\QuizQuestion;
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
        /*$this->quiz->sendToDiscord('question_announce', [
            '%question_difficulty' => $this->question->difficulty,
            '%question_theme' => $this->question->theme->name,
        ]);*/
        sleep(5);

        $start_time = new \DateTime();
        $end_time = new \DateTime();
        $end_time->modify('+ ' . $this->quiz->delay . ' minutes');

        //On empeche de déclencher deux fois la question
        if (!empty($this->start_time)) return;

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

        $user = \App\User::firstOrCreate(
            ['discord_id' => $args['user_discord_id']],
            ['name' => $args['user_name'], 'password' => Hash::make(str_random(20))]
        );
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

        if ($answer->isCorrect()) {
            $this->close();
        } else {
            $rand = rand(1, 5);
            if ($rand === 1) {
                $this->quiz->sendToDiscord('question_answer_wrong', ['%user' => $user->name], $guild);
            }
        }
    }

    public function close()
    {
        if (!empty($this->correctAnswer)) {
            if ($this->quiz->event->multi_guilds) {
                $this->quiz->sendToDiscord('question_answer_correct', [
                    '%user' => $this->correctAnswer->user->name,
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
                    '%user' => $this->correctAnswer->user->name,
                    '%answer' => $this->question->answer,
                ]);
            }
            if (!empty($this->question->explanation)) {
                $this->quiz->sendToDiscord('question_answer_explanation', [
                    '%answer' => $this->question->answer,
                    '%explanation' => $this->question->explanation
                ]);
                sleep(10);
            }
        } else {
            $this->quiz->sendToDiscord('question_not_answered');
        }
        $this->quiz->nextQuestion();
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