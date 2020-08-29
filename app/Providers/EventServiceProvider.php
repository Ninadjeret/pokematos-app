<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        'App\Events\RaidEnded' => [
            'App\Listeners\PurgeDiscordRaidData',
        ],
        'App\Events\RaidCreated' => [
            'App\Listeners\PostRaidToDiscord',
            'App\Listeners\DeleteDiscordMessage',
        ],
        'App\Events\RaidUpdated' => [
            'App\Listeners\PurgeDiscordRaidData',
            'App\Listeners\PostRaidToDiscord',
            'App\Listeners\DeleteDiscordMessage'
        ],
        'App\Events\RaidDuplicate' => [
            'App\Listeners\DeleteDiscordMessage'
        ],
        'App\Events\RaidDeleted' => [
            'App\Listeners\PurgeDiscordRaidData',
        ],
        'App\Events\QuestInstanceCreated' => [
            'App\Listeners\PostQuestInstanceToDiscord',
        ],
        'App\Events\QuestInstanceUpdated' => [
            'App\Listeners\PurgeDiscordQuestInstanceData',
            'App\Listeners\PostQuestInstanceToDiscord',
        ],
        'App\Events\QuestInstanceDeleted' => [
            'App\Listeners\PurgeDiscordQuestInstanceData',
        ],
        'App\Events\RocketInvasionCreated' => [
            'App\Listeners\PostInvasionToDiscord',
        ],
        'App\Events\RocketInvasionUpdated' => [
            'App\Listeners\PurgeDiscordInvasionData',
            'App\Listeners\PostInvasionToDiscord',
        ],
        'App\Events\RocketInvasionDeleted' => [
            'App\Listeners\PurgeDiscordInvasionData',
        ],

        //Events
        'App\Events\Events\EventCreated' => [],
        'App\Events\Events\EventDeleted' => [
            'App\Listeners\Discord\DeleteChannel',
        ],
        'App\Events\Events\EventEnded' => [
            'App\Listeners\Discord\DeleteChannel',
        ],
        'App\Events\Events\TrainCreated' => [
            'App\Listeners\Discord\PostTrainMessage',
        ],
        'App\Events\Events\TrainUpdated' => [
            'App\Listeners\Discord\PostTrainMessage',
        ],
        'App\Events\Events\TrainStepChecked' => [
            'App\Listeners\Discord\PostTrainMessage',
        ],
        'App\Events\Events\TrainStepUnchecked' => [
            'App\Listeners\Discord\DeleteMessage',
        ],
        'App\Events\Events\InvitAccepted' => [
            'App\Listeners\Discord\CreateChannel',
        ],
        'App\Events\Events\InvitCanceled' => [
            'App\Listeners\Discord\DeleteChannel',
        ],
        'App\Events\Events\InvitRefused' => [
            'App\Listeners\Discord\DeleteChannel',
        ],

        //Courant
        'App\Events\DayChanged' => [
            'App\Listeners\PurgeDiscordQuestInstanceData',
            'App\Listeners\PurgeDiscordInvasionData',
            'App\Listeners\POI\TouchPOIs',
            'App\Listeners\Events\PurgeEvents',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}