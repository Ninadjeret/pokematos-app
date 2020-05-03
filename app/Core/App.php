<?php

namespace App\Core;

use App\Models\Role;
use RestCord\DiscordClient;
use Illuminate\Support\Facades\Log;

class App {

    public static function config() {
        $config = [
            'version' => config('app.version'),
            'config' => [
                'raids' => [
                    'timing_before_eclosion' => intval(env('RAIDS_TIMING_BEFORE_ECLOSION', -60)),
                    'timing_after_eclosion' => intval(env('RAIDS_TIMING_AFTER_ECLOSION', 45)),
                ],
                'alerts' => [
                    'modal' => env('ALERTS_MODAL',''),
                ]
            ],
            'donation' => [
                'goal' => 415,
                'current' => 237,
            ],
            'features' => config('features'),
        ];
        return $config;
    }

}
