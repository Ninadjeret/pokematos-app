<?php

namespace App\Core;

use App\Models\Role;
use RestCord\DiscordClient;
use Illuminate\Support\Facades\Log;

class App
{

    public static function config()
    {
        $settings = \App\Models\Setting::get();
        $config = [
            'version' => config('app.version'),
            'config' => [
                'raids' => [
                    'timing_before_eclosion' => intval("-{$settings->timing_before_eclosion}"),
                    'timing_after_eclosion' => intval($settings->timing_after_eclosion),
                ],
                'alerts' => [
                    'modal' => env('ALERTS_MODAL', ''),
                ]
            ],
            'donation' => [
                'goal' => env('DONATION_GOAL', '0'),
                'current' => env('DONATION_AMOUNT', '0'),
            ],
            'features' => config('features'),
            'beta' => explode(',', env('BETA_GUILDS', '')),
        ];
        return $config;
    }
}
