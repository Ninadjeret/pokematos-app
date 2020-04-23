<?php

namespace App\Models;

use App\Models\Event;
use App\Models\QuizTheme;
use RestCord\DiscordClient;
use App\Models\QuizQuestion;
use App\Models\EventQuizQuestion;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;

class EventQuiz extends Model
{
    protected $table = 'event_quizs';
    protected $fillable = ['event_id', 'nb_questions', 'delay', 'themes', 'difficulties', 'only_pogo', 'message_discord_id'];
    protected $appends = ['questions'];

    public function getQuestionsAttribute() {
        return EventQuizQuestion::where('quiz_id', $this->id)->orderBy('order', 'ASC')->get();
    }

    public function getEventAttribute() {
        return Event::find($this->event_id);
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

    public function process() {
        Log::debug('process');
        if( $this->isEnded() ) {
            Log::debug('close');
            $this->close();
        } elseif( !$this->isStarted() ) {
            Log::debug('start');
            $this->start();
        } else {
            Log::debug('getLastQuestion');
            $question = $this->getLastQuestion();
            if( $question && $question->isEnded() ) {
                Log::debug('nextQuestion');
                $question->close();
                $this->nextQuestion();
            }
        }
    }

    public function start() {

        if( !empty($this->event->channel_discord_id) ) {
            $this->sendToDiscord('Test');
            sleep(3);
            $this->sendToDiscord('Tutu');
        }

        $question = EventQuizQuestion::where('quiz_id', $this->id)
            ->orderBy('order', 'ASC')
            ->first();
        if( $question) $question->start();
    }

    public function close() {
        $classement = $this->getRanking();
        $num = 0;
        $ranking = '';
        $best_player = '';
        foreach( $classement as $user => $responses ) {
            $num++;
            if( $num === 1 ) $best_player = $user;
            if( $num === 1 ) $ranking .= ":first_place: ";
            if( $num === 2 ) $ranking .= ":second_place: ";
            if( $num === 3 ) $ranking .= ":third_place: ";
                $ranking .= "{$user} : **{$responses}**\r\n";
        }

        $this->sendToDiscord('Bravo pour ce super quiz !');
        sleep(1);
        $this->sendToDiscord('Voici les résultats :point_down:');
        sleep(1);
        $this->sendToDiscord("**----------\r\nClassement définitif\r\n----------**\r\n{$ranking}");
        sleep(1);
        $this->sendToDiscord("Féliciations à @{$best_player}");
    }

    public function isStarted() {
        $questions = EventQuizQuestion::where('quiz_id', $this->id)
            ->whereNotNull('start_time')
            ->orderBy('order', 'ASC')
            ->get();
        return (!empty($questions->toArray()));
    }

    public function isEnded() {
        $question = EventQuizQuestion::where('quiz_id', $this->id)
            ->orderBy('order', 'DESC')
            ->first();
        if( empty($question->end_time) ) {
            return false;
        }
        if( !empty($question->correctAnswer) ) {
            return true;
        }
        $now = new \DateTime();
        $end_time = new \DateTime($question->end_time);
        return ( $now > $end_time );
    }

    public function getLastQuestion() {
        $question = EventQuizQuestion::where('quiz_id', $this->id)
            ->whereNotNull('start_time')
            ->orderBy('order', 'DESC')
            ->first();
        return $question;
    }

    public function nextQuestion() {
        $question = EventQuizQuestion::where('quiz_id', $this->id)
            ->whereNull('start_time')
            ->orderBy('order', 'ASC')
            ->first();
        if( $question) $question->start();
    }

    public function addAnswer($args) {
        if( !$this->isStarted() || $this->isEnded() ) return false;

        $question = $this->getLastQuestion();
        if( $question->isEnded() ) return false;
        $question->addAnswer($args);
    }

    public function sendToDiscord($content) {
        $content = \App\Helpers\Discord::encode($content, $this->event->guild, false);
        $discord = new DiscordClient(['token' => config('discord.token')]);
        $message = $discord->channel->createMessage(array(
            'channel.id' => intval($this->event->channel_discord_id),
            'content' => $content,
        ));
    }

    public function getRanking() {
        $classement = [];
        foreach( $this->questions as $question ) {
            if( empty( $question->correctAnswer ) ) continue;
            $user_name = $question->correctAnswer->user->name;
            $points = $question->question->difficulty;
            if( array_key_exists($user_name, $classement) ) {
                $classement[$user_name] += $points;
            } else {
                $classement[$user_name] = $points;
            }
        }
        asort($classement);
        return $classement;
    }
}
