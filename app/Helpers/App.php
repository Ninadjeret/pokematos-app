<?php

namespace App\Helpers;

use App\Models\Role;
use RestCord\DiscordClient;
use Illuminate\Support\Facades\Log;

class App {

    public static function config() {
        $config = [
            'version' => env('APP_VERSION', '1.0.0'),
            'config' => [
                'raids' => [
                    'timing_before_eclosion' => env('RAIDS_TIMING_BEFORE_ECLOSION', -60),
                    'timing_after_eclosion' => env('RAIDS_TIMING_AFTER_ECLOSION', 45),
                ],
                'alerts' => [
                    'modal' => 'Suite aux règles de confinement actuellement en vigueur, et pour ne pas inciter les joueurs à sé déplacer, les annonces sont actuellement désactivées.',
                ]
            ],
            'donation' => [
                'goal' => 415,
                'current' => 216,
            ],
            'features' => [
                'events' => env('FEATURES_EVENTS', true),
                'rocket' => env('FEATURES_ROCKET', true),
                'raid_reporting' => env('FEATURES_RAID_REPORTING', true),
                'raidex_reporting' => env('FEATURES_RAIDEX_REPORTING', true),
                'quest_reporting' => env('FEATURES_QUEST_REPORTING', true),
            ]
        ];
        return $config;
    }

}
