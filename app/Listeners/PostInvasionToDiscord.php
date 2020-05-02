<?php

namespace App\Listeners;

use App\Models\Stop;
use App\Models\City;
use App\Core\Helpers;
use App\Models\RaidMessage;
use App\Models\RocketConnector;
use Illuminate\Support\Facades\Log;
use App\Events\RocketInvasionCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class PostInvasionToDiscord
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
     * @param  RocketInvasionUpdated  $event
     * @return void
     */
    public function handle($event)
    {
        $city = City::find( $event->invasion->city_id );
        $gym = Stop::find( $event->invasion->stop_id );
        $zone_id = ( $gym->zone_id ) ? $gym->zone_id : false ;
        $gym_id = $gym->id;
        $boss = $event->invasion->boss;


        $connectors = RocketConnector::whereIn( 'guild_id', $city->getGuildsIds() )
            ->get();

        foreach( $connectors as $connector ) {

            if( $connector->filter_stop_type == 'zone' ) {
                if( !$zone_id ) continue;
                if( !in_array( $zone_id, $connector->filter_stop_zone ) ) continue;
            }

            if( $connector->filter_stop_type == 'stop' ) {
                if( !$gym_id ) continue;
                if( !in_array( $gym_id, $connector->filter_stop_stop ) ) continue;
            }

            if( $connector->filter_boss_type == 'boss' ) {
                if( !in_array( $event->invasion->boss->id, $connector->filter_boss_bosses ) ) continue;
            }

            $connector->postMessage( $event->invasion, $event->announce );
        }

    }
}
