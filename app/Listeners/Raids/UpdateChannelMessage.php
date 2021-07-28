<?php

namespace App\Listeners\Raids;

use App\Models\RaidGroup;
use App\Events\RaidUpdated;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateChannelMessage
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
     * @param  RaidUpdated  $event
     * @return void
     */
    public function handle(RaidUpdated $event)
    {
        $raid_groups = RaidGroup::where('raid_id', $event->raid->id)->get();
        if( empty($raid_groups) ) return;
        foreach( $raid_groups as $raid_group ) {
            $raid_group->updateChannelMessage();
        }
    }
}
