<?php

namespace App\Listeners;

use App\Models\Stop;
use App\Models\City;
use App\Models\QuestConnector;
use App\Models\QuestMessage;
use App\Events\QuestInstanceCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class PostQuestInstanceToDiscord
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
     * @param  QuestInstanceCreated  $event
     * @return void
     */
    public function handle(QuestInstanceCreated $event)
    {
        $city = City::find( $event->quest->city_id );

        $gym = Stop::find( $event->quest->gym_id );
        $zone_id = ( $gym->zone_id ) ? $gym->zone_id : false ;
        $gym_id = $gym->id ;
        $pokemon_id = ( $event->quest->quest->pokemon_id ) ? $event->quest->quest->pokemon_id : false;

        $connectors = QuestConnector::whereIn( 'guild_id', $city->getGuildsIds() )
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

            if( $connector->filter_reward_type == 'reward' ) {
                if( !$event->quest->quest ) continue;
                if( !in_array( $event->raid->egg_level, $connector->filter_reward_reward ) ) continue;
            }

            if( $connector->filter_reward_type == 'pokemon' ) {
                if( !$pokemon_id ) continue;
                if( !in_array( $pokemon_id, $connector->filter_reward_pokemon ) ) continue;
            }

            $connector->postMessage( $event->quest, $event->announce );

        }

    }
}
