<?php

namespace App\Helpers;

use App\Models\Role;
use RestCord\DiscordClient;
use Illuminate\Support\Facades\Log;

class Conversation {

    public static $quiz = [
        [
            'message' => 'Aucune idée, mais je veux bien demander à mon créateur',
            'type' => 'question',
            'suite' => ['Il comprend plus de choses que moi', '(heureusement :wink:)'],
        ],
    ];

    public static function getRandomMessage( $type ) {

    }

}
