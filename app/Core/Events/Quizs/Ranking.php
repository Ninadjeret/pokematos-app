<?php

namespace App\Core\Events\Quizs;

use App\Models\Role;
use RestCord\DiscordClient;
use Illuminate\Support\Facades\Log;

class Ranking {

    public function __construct( $questions, $final = false ) {
        $this->questions = $questions;
    }

    /**
     * [getMultiRanking description]
     * @return [type] [description]
     */
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

    /**
     * [formatMultiRanking description]
     * @return [type] [description]
     */
    public function formatMultiRanking() {
        $ranking = "";
        $data = $this->getMultiRanking();
        $num = 0;
        $previous_player = false;
        $revisous_score = false;

        foreach( $data as $user => $score ) {
            $num++;
            if( $num === 1 ) $ranking_pos = ":first_place: ";
            if( $num === 2 ) $ranking_pos = ":second_place: ";
            if( $num === 3 ) $ranking_pos = ":third_place: ";
            if( $num > 3 ) $ranking_pos = "{$num} : ";
            if( $revisous_score === $score ) $ranking_pos = $previous_ranking;
            $ranking .= "{$ranking_pos} {$user} : **{$score}**\r\n";

            $previous_player = $user;
            $revisous_score = $score;
            $previous_ranking = $ranking_pos;
        }

        return $ranking;
    }

    /**
     * [getRanking description]
     * @return [type] [description]
     */
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

    /**
     * [formatRanking description]
     * @return [type] [description]
     */
    public function formatRanking() {
        $classement = $this->getRanking();
        $num = 0;
        $ranking = '';
        $previous_player = false;
        $revisous_score = false;

        foreach( $classement as $user => $score ) {
            $num++;
            if( $num === 1 ) $ranking_pos = ":first_place: ";
            if( $num === 2  ) $ranking_pos = ":second_place: ";
            if( $num === 3 ) $ranking_pos = ":third_place: ";
            if( $num > 3 ) $ranking_pos = "{$num} ";
            if( $revisous_score === $score ) $ranking_pos = $previous_ranking;
            $ranking .= "{$ranking_pos} {$user} : **{$score}**\r\n";

            $previous_player = $user;
            $revisous_score = $score;
            $previous_ranking = $ranking_pos;
        }

        return $ranking;
    }

    /**
     * [getConversationsLabels description]
     * @return [type] [description]
     */
    public function getConversationsLabels() {

    }

    /**
     * [getBestGuild description]
     * @return [type] [description]
     */
    public function getBestGuild() {
        $classement = $this->getMultiRanking();
        return array_key_first($classement);
    }

    /**
     * [getBestPlayer description]
     * @return [type] [description]
     */
    public function getBestPlayer() {
        $classement = $this->getRanking();
        return array_key_first($classement);
    }

    /**
     * [getTotalPoints description]
     * @return [type] [description]
     */
    public function getTotalPoints() {
        $points = 0;
        foreach( $this->questions as $question ) {
            $points += $question->question->difficulty;
        }
        return $points;
    }

}
