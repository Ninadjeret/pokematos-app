<?php

namespace App\Listeners;

use App\Models\Stop;
use App\Models\City;
use App\Models\Connector;
use App\Models\RaidMessage;
use App\Events\RaidCreated;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class PostRaidToDiscord
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
     * @param  RaidCreated  $event
     * @return void
     */
    public function handle($event) {
        $city = City::find( $event->raid->city_id );

        $gym = Stop::find( $event->raid->gym_id );
        $zone_id = ( $gym->zone_id ) ? $gym->zone_id : false ;
        $gym_id = $gym->id ;
        $pokemon_id = ( $event->raid->pokemon_id ) ? $event->raid->pokemon_id : false;

        $connectors = Connector::whereIn( 'guild_id', $city->getGuildsIds() )
            ->get();

        foreach( $connectors as $connector ) {

            if( $connector->filter_gym_type == 'zone' ) {
                if( !$zone_id ) continue;
                if( !in_array( $zone_id, $connector->filter_gym_zone ) ) continue;
            }

            if( $connector->filter_gym_type == 'gym' ) {
                if( !$gym_id ) continue;
                if( !in_array( $gym_id, $connector->filter_gym_gym ) ) continue;
            }

            if( $connector->filter_pokemon_type == 'level' ) {
                if( !$event->raid->egg_level ) continue;
                if( !in_array( $event->raid->egg_level, $connector->filter_pokemon_level ) ) continue;
            }

            if( $connector->filter_pokemon_type == 'pokemon' ) {
                if( !$pokemon_id ) continue;
                if( !in_array( $pokemon_id, $connector->filter_pokemon_pokemon ) ) continue;
            }

            if( !empty($connector->filter_source_type) ) {
<<<<<<< HEAD
                if( !in_array( $event->raid->getLastUserAction( $include_auto = true )->source, $connector->filter_source_type ) ) continue;
=======
                if( !in_array( $event->raid->getLastAnnounce($include_auto = true)->source, $connector->filter_source_type ) ) continue;
>>>>>>> 4fedcf2df484f1c0a244d9b35089c73e727d624b
            }

            $connector->postMessage( $event->raid, $event->announce );

        }

    }


}
