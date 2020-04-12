<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Event;
use App\Models\Guild;
use App\Helpers\Helpers;
use App\Models\EventTrain;
use Illuminate\Http\Request;
use App\Models\EventTrainStep;
use Illuminate\Support\Facades\Log;
use App\Events\Events\EventDeleted;
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

    public function getGuildEvents( Request $request, Guild $guild ) {
        $user = Auth::user();
        if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }

        $events = Event::where('guild_id', $guild->id)->get();

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
            'guild_id' => $guild->id,
            'city_id' => $guild->city->id,
            'type' => $request->type,
            'start_time' => $request->start_time,
        ];

        if( !empty($request->steps) ) {
            $args['steps'] = $request->steps;
        }

        $event = Event::add($args);
        return response()->json($event, 200);
    }

    public  function updateEvent( Request $request, Guild $guild, Event $event ) {
        $user = Auth::user();
        if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }

        $args['event'] = [
            'name' => $request->name,
            'type' => $request->type,
            'start_time' => $request->start_time
        ];
        if( !empty($request->steps) ) {
            $args['steps'] = $request->steps;
        }
        $event->change($args);

        return response()->json($event, 200);
    }

    public function deleteEvent(Request $request, Guild $guild, Event $event ) {
        $user = Auth::user();
        if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }

        $event_id = $event->id;
        event(new EventDeleted($event, $event->guild));
        Event::destroy($event_id);

        $train = EventTrain::where('event_id', $event_id)->first();
        if( $train ) {
            $train_id = $train->id;
            EventTrain::destroy($train_id);

            $steps = EventTrainStep::where('train_id', $train_id)->get();
            if( !empty($steps) ) {
                EventTrainStep::destroy($steps);
            }
        }

        return response()->json(null, 204);
    }

    public function checkStep(Request $request, Guild $guild, Event $event, EventTrainStep $step ) {
        $user = Auth::user();
        if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
        $step->check();
        return response()->json($event, 200);
    }

    public function uncheckStep(Request $request, Guild $guild, Event $event, EventTrainStep $step ) {
        $user = Auth::user();
        if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
        $step->uncheck();
        return response()->json($event, 200);
    }

}
