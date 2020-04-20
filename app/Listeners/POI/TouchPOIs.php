<?php

namespace App\Listeners\POI;

use App\Events\DayChanged;
use App\Models\QuestInstance;
use App\Models\RocketInvasion;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class TouchPOIs
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  DayChanged  $event
     * @return void
     */
    public function handle(DayChanged $event)
    {
        $quests = QuestInstance::where('date', $event->yesterday->format('Y-m-d').' 00:00:00')->get();
        foreach( $quests as $quest ) {
            $quest->getStop()->touch();
        }

        $invasions = RocketInvasion::where('date', $event->yesterday->format('Y-m-d'))->get();
        foreach( $invasions as $invasion ) {
            $invasion->getStop()->touch();
        }
    }
}
