<?php

namespace App\Models;

use App\Models\Event;
use GuzzleHttp\Client;
use App\Models\QuizTheme;
use RestCord\DiscordClient;
use App\Models\QuizQuestion;
use App\Helpers\Conversation;
use App\Models\EventQuizQuestion;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;

class EventQuiz extends Model
{
    protected $table = 'event_quizs';
    protected $fillable = ['event_id', 'nb_questions', 'delay', 'themes', 'difficulties', 'only_pogo', 'message_discord_id', 'status'];
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

        $query = QuizQuestion::whereIn('difficulty', $difficulties)
            ->whereIn('theme_id', $themes)
            ->inRandomOrder()
            ->take($this->nb_questions);
        if( $this->only_pogo ) $query->where('only_pogo', 1);
        $questions = $query->get();

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
        if( $this->isClosed() || !$this->hasToStart() ) {
            return;
        } elseif( $this->isEnded() ) {
            $this->close();
        } elseif( $this->hasToStart() ) {
            $this->start();
        } else {
            $question = $this->getLastQuestion();
            if( $question && $question->isEnded() ) {
                $question->close();
                $this->nextQuestion();
            }
        }
    }

    public function start() {

        $this->update(['status' => 'active']);

        //On avertit le bot de la MAJ
        $client = new Client();
        $url = config('app.bot_sync_url');
        if( !empty($url) ) {
            $res = $client->get($url);
        }

        $this->sendToDiscord('start_intro', [
            '%quiz_name' => $this->event->name,
        ]);
        sleep(3);
        $this->sendToDiscord( 'start_description', [
            '%quiz_name' => $this->event->name,
            '%quiz_nb_questions' => $this->nb_questions,
            '%quiz_delay' => $this->delay
        ]);
        sleep(10);
        $this->sendToDiscord('start_warning');

        $question = EventQuizQuestion::where('quiz_id', $this->id)
            ->orderBy('order', 'ASC')
            ->first();
        if($question) $question->start();
    }

    public function close() {

        $this->update(['status' => 'closed']);

        //On avertit le bot de la MAJ
        $client = new Client();
        $url = config('app.bot_sync_url');
        if( !empty($url) ) {
            $res = $client->get($url);
        }

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

    public function hasToStart() {
        $now = new \DateTime();
        $start_time = new \DateTime($this->start_time);
        if( $this->status == 'future' && $now > $start_time ) {
            return true;
        }
        return false;
    }

    public function isStarted() {
        return $this->status == 'active';
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

    public function isClosed() {
        return $this->status == 'closed';
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

    public function sendToDiscord( $type, $args = null, $guild = null) {
        $to_send = [];

        if( !empty($this->event->channel_discord_id) ) {
            if( empty($guild) || $guild->id == $this->event->guild_id ) {
                $to_send[$this->event->channel_discord_id] = $this->event->guild;
            }
        }

        if( $this->event->multi_guilds ) {
            foreach( $this->event->guests as $guest ) {
                if( $guest->status != 'accepted' ) continue;
                if( !empty($guild) && $guild->id != $guest->guild_id ) continue;
                if( !empty($guest->channel_discord_id) ) $to_send[$guest->channel_discord_id] = $guest->guild;
            }
        }

        if( empty($to_send) ) return;

        foreach( $to_send as $channel_id => $guild ) {
            $message = \App\Helpers\Conversation::sendToDiscord($channel_id, $guild, 'quiz', $type, $args);
        }
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
        arsort($classement);
        return $classement;
    }

    public function getMultiRanking() {
        $classement = [];
        foreach( $this->questions as $question ) {
            if( empty( $question->correctAnswer ) ) continue;
            $guild_name = $question->correctAnswer->guild->name;
            $points = $question->question->difficulty;
            if( array_key_exists($guild_name, $classement) ) {
                $classement[$guild_name] += $points;
            } else {
                $classement[$guild_name] = $points;
            }
        }
        arsort($classement);
        return $classement;
    }

    public function formatMultiRanking() {
        $ranking = "";
        $data = $this->getMultiRanking();
        $num = 0;
        foreach( $data as $user => $responses ) {
            $num++;
            if( $num === 1 ) $ranking .= ":first_place: ";
            if( $num === 2 ) $ranking .= ":second_place: ";
            if( $num === 3 ) $ranking .= ":third_place: ";
            if( $num > 3 ) $ranking .= "{$num} : ";
            $ranking .= "{$user} : **{$responses}**\r\n";
        }
        return $ranking;
    }
}
