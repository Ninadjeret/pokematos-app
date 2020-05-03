<?php

namespace App\Listeners\Events;

use App\Models\Event;
use App\Events\DayChanged;
use App\Events\EventEnded;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class PurgeEvents
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
        $events = Event::where('end_time', $event->yestarday->format('Y-m-d').' 23:59:00');
        if( empty( $events ) ) {
            foreach( $events as $event_item ) {
                event(new EventEnded($event_item, $event_item->guild));
            }
        }
    }
}
