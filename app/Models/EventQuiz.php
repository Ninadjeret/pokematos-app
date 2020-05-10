<?php

namespace App\Models;

use App\Models\Event;
use GuzzleHttp\Client;
use App\Models\QuizTheme;
use App\Core\Conversation;
use RestCord\DiscordClient;
use App\Models\QuizQuestion;
use App\Models\EventQuizQuestion;
use App\Core\Events\Quizs\Ranking;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;

class EventQuiz extends Model
{
    protected $table = 'event_quizs';
    protected $fillable = ['event_id', 'nb_questions', 'delay', 'themes', 'difficulties', 'only_pogo', 'message_discord_id', 'status'];
    protected $appends = ['questions'];
    protected $casts = [
        'themes' => 'array',
        'difficulties' => 'array',
    ];


    /**
     * [getQuestionsAttribute description]
     * @return [type] [description]
     */
    public function getQuestionsAttribute() {
        return EventQuizQuestion::where('quiz_id', $this->id)->orderBy('order', 'ASC')->get();
    }


    /**
     * [getEventAttribute description]
     * @return [type] [description]
     */
    public function getEventAttribute() {
        return Event::find($this->event_id);
    }

    public function getThemesAttribute($value) {
        if( empty($value) ) return [];
        return json_decode($value);
    }

    public function getDifficultiesAttribute($value) {
        if( empty($value) ) return [];
        return json_decode($value);
    }


    /**
     * [shuffleQuestions description]
     * @return [type] [description]
     */
    public function shuffleQuestions() {

        EventQuizQuestion::where('quiz_id', $this->id)->delete();

        $difficulties = ( empty($this->difficulties) ) ? [1, 2, 3, 5] : $this->difficulties ;
        $themes = ( empty($this->themes) ) ? \DB::table('quiz_themes')->pluck('id')->toArray() : $this->themes ;

        $query = QuizQuestion::whereIn('difficulty', $difficulties)
            ->whereIn('theme_id', $themes)
            ->inRandomOrder()
            ->take($this->nb_questions);
        if( $this->only_pogo ) $query->where('about_pogo', 1);
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


    /**
     * [getNbQuestions description]
     * @return [type] [description]
     */
    public function getNbQuestions() {
        return count($this->questions);
    }


    /**
     * [process description]
     * @return [type] [description]
     */
    public function process() {
        if( $this->isClosed() ) {
            Log::debug('isClosed');
            return;
        } elseif( $this->isEnded() ) {
            Log::debug('isEnded');
            $this->close();
        } elseif( $this->hasToStart() ) {
            Log::debug('hasToStart');
            $this->start();
        } else {
            Log::debug('else');
            $question = $this->getLastQuestion();
            if( $question && $question->isEnded() ) {
                $question->close();
            }
        }
    }


    /**
     * [start description]
     * @return [type] [description]
     */
    public function start() {

        $this->update(['status' => 'active']);

        //On avertit le bot de la MAJ
        \App\Core\Discord::SyncBot();

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


    /**
     * [close description]
     * @return [type] [description]
     */
    public function close() {

        $this->update(['status' => 'closed']);

        //On avertit le bot de la MAJ
        \App\Core\Discord::SyncBot();

        $this->sendToDiscord('quiz_ended');
        sleep(5);
        if( $this->event->multi_guilds ) {
            $ranking = new Ranking($this->questions);
            $this->sendToDiscord('quiz_final_score_guilds', [
                '%ranking' => $ranking->formatMultiRanking(),
                '%best_guild' => $ranking->getBestGuild(),
            ], null, [
                'title' => "Serveurs - Classement définitif",
                'thumbnail' => EventQuiz::getEmbedThumbnails()->ranking,
            ]);
        }

        $ranking = new Ranking($this->questions);
        $this->sendToDiscord('quiz_final_score_players', [
            '%ranking' => $ranking->formatRanking(),
            '%best_player' => $ranking->getBestPlayer(),
        ], null, [
            'title' => "Joueurs - Classement définitif",
            'thumbnail' => EventQuiz::getEmbedThumbnails()->ranking,
        ]);
    }


    /**
     * [hasToStart description]
     * @return boolean [description]
     */
    public function hasToStart() {
        $now = new \DateTime();
        $start_time = new \DateTime($this->event->start_time);
        if( $this->status == 'future' && $now > $start_time ) {
            return true;
        }
        return false;
    }


    /**
     * [isStarted description]
     * @return boolean [description]
     */
    public function isStarted() {
        return $this->status == 'active';
    }


    /**
     * [isEnded description]
     * @return boolean [description]
     */
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


    /**
     * [isClosed description]
     * @return boolean [description]
     */
    public function isClosed() {
        return $this->status == 'closed';
    }


    /**
     * [getLastQuestion description]
     * @return [type] [description]
     */
    public function getLastQuestion() {
        $question = EventQuizQuestion::where('quiz_id', $this->id)
            ->whereNotNull('start_time')
            ->orderBy('order', 'DESC')
            ->first();
        return $question;
    }


    /**
     * [nextQuestion description]
     * @return [type] [description]
     */
    public function nextQuestion() {
        $question = EventQuizQuestion::where('quiz_id', $this->id)
            ->whereNull('start_time')
            ->orderBy('order', 'ASC')
            ->first();
        if($question) {
            $question->start();
        } else {
            $this->close();
        }
    }


    /**
     * [addAnswer description]
     * @param [type] $args [description]
     */
    public function addAnswer($args) {
        if( !$this->isStarted() || $this->isEnded() ) return false;

        $question = $this->getLastQuestion();
        if( $question->isEnded() ) return false;
        $question->addAnswer($args);
    }


    /**
     * [sendToDiscord description]
     * @param  [type] $type  [description]
     * @param  [type] $args  [description]
     * @param  [type] $guild [description]
     * @param  [type] $embed [description]
     * @return [type]        [description]
     */
    public function sendToDiscord( $type, $args = null, $guild = null, $embed = null) {
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
            $message = \App\Core\Conversation::sendToDiscord($channel_id, $guild, 'quiz', $type, $args, $embed);
        }
    }


    public static function getEmbedThumbnails() {
        return (object) [
                'question' => 'https://assets.profchen.fr/img/app/event_quiz_question.png',
                'ranking' => 'https://assets.profchen.fr/img/app/event_quiz_ranking.png'
        ];
    }
}
