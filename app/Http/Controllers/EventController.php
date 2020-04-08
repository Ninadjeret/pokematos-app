<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Event;
use App\Models\Guild;
use App\Helpers\Helpers;
use Illuminate\Http\Request;
use App\Models\EventTrainStep;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function getActiveEvents( Request $request, City $city ) {
        $user = Auth::user();

        $user_guild_ids = Helpers::extractIds( $user->getGuilds() );
        $city_guild_ids = $city->getGuildsIds();
        $matching_ids = array_intersect( $user_guild_ids, $city_guild_ids );

        $events = Event::where('end_time', '>', date('Y-m-d H:i:s'))
            ->whereIn('guild_id', $matching_ids)    
            ->get(); 

        return response()->json($events, 200);
    }

    public function getEvent( Request $request, City $city, Event $event ) {
        $user = Auth::user();

        $user_guild_ids = Helpers::extractIds( $user->getGuilds() );
        if( !in_array($event->guild_id, $user_guild_ids) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);    
        }

        return response()->json($event, 200);
    }

    public  function createEvent( Request $request, Guild $guild ) {
        $user = Auth::user();
        if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }

        $args = [];
        $args['event'] = [
            'name' => $request->name,
            'guid_id' => $guild->id,
            'city_id' => $guild->city->id,
            'type' => $request->type,
            'relation_id' => $request->relation_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'channel_discord_id' => $request->channel_discord_id,
        ];

        if( property_exists('steps', $request) && !empty($request->steps) ) {
            $args['steps'] = [];
            foreach( $request->steps as $step ) {
                $args['steps'][] =[
                    'type' => $step->type,
                    'stop_id' => $step->stop_id,
                    'start_time' => $step->start_time,
                    'duration' => $step->duration,
                    'description' => $step->description,
                ];
            }
        }

        $event = Event::add($args);
        return response()->json($event, 200);
    }

    public  function updateEvent( Request $request, Guild $guild, Event $event ) {
        $user = Auth::user();
        if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
        return response()->json($event, 200);
    }

    public function deleteEvent(Request $request, Guild $guild, Event $event ) {
        $user = Auth::user();
        if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
        Event::destroy($event->id);
        return response()->json(null, 204);
    }

    public function checkStep(Request $request, Guild $guild, Event $event, EventTrainStep $step ) {
        $user = Auth::user();
        if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
        $step->update(['checked' => 1]);
        return response()->json($event, 200);
    }

    public function uncheckStep(Request $request, Guild $guild, Event $event, EventTrainStep $step ) {
        $user = Auth::user();
        if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
        $step->update(['checked' => 0]);
        return response()->json($event, 200);
    }

}
