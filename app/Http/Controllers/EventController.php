<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function getActiveEvents( Request $request, Guild $guild ) {
        $events = Events::where('end_time', '>', date('Y-m-d H:i:s'))->get(); 
        return response()->json($events, 200);
    }

    public function getEvent( Request $request, Guild $guild, Event $event ) {
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

        }

        $event = Event::add($args);
        return response()->json($event, 200);
    }

}
