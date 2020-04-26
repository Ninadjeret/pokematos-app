<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Event;
use App\Models\Guild;
use App\Helpers\Helpers;
use App\Models\EventQuiz;
use App\Models\EventInvit;
use App\Models\EventTrain;
use Illuminate\Http\Request;
use App\Models\EventTrainStep;
use Illuminate\Support\Facades\Log;
use App\Events\Events\EventDeleted;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public static $feature = 'features.events';
    public static $feature_message = 'La fonctionnalité n\'est pas active';

    public function getActiveEvents( Request $request, City $city ) {
        $user = Auth::user();

        $user_guild_ids = Helpers::extractIds( $user->getGuilds() );
        $city_guild_ids = $city->getGuildsIds();
        $matching_ids = array_intersect( $user_guild_ids, $city_guild_ids );

        $events = Event::where('end_time', '>', date('Y-m-d H:i:s'))
            ->whereIn('guild_id', $matching_ids)
            ->get();

        $invits = Event::where('end_time', '>', date('Y-m-d H:i:s'))
            ->whereHas('invits', function($q) use ($matching_ids) {
                $q->where('status', 'accepted')->whereIn('guild_id', $matching_ids);
            })
            ->get();
        $merged = $events->merge($invits);
        return response()->json($merged->all(), 200);
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
        if( !config(self::$feature) )return response()->json(self::$feature_message, 403);
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
            'image' => $request->image,
        ];

        if( !empty($request->steps) ) {
            $args['steps'] = $request->steps;
        }

        if( !empty($request->quiz) ) {
            $args['quiz'] = $request->quiz;
        }

        $event = Event::add($args);
        return response()->json($event, 200);
    }

    public  function updateEvent( Request $request, Guild $guild, Event $event ) {
        if( !config(self::$feature) )return response()->json(self::$feature_message, 403);
        $user = Auth::user();
        if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }

        $args['event'] = [
            'name' => $request->name,
            'type' => $request->type,
            'start_time' => $request->start_time,
            'image' => $request->image,
            'multi_guilds' => $request->multi_guilds,
        ];
        if( !empty($request->steps) ) {
            $args['steps'] = $request->steps;
        }
        if( !empty($request->quiz) ) {
            $args['quiz'] = $request->quiz;
        }
        $args['guests'] = $request->guests;
        $event->change($args);

        return response()->json($event, 200);
    }

    public function deleteEvent(Request $request, Guild $guild, Event $event ) {
        $user = Auth::user();
        if( !$user->can('guild_manage', ['guild_id' => $guild->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }

        $event_id = $event->id;

        if( $event->type == 'train' ) {
            $train = EventTrain::where('event_id', $event_id)->first();
            if( $train ) {
                $train_id = $train->id;
                EventTrain::destroy($train_id);

                $steps = EventTrainStep::where('train_id', $train_id)->get();
                if( !empty($steps) ) {
                    EventTrainStep::destroy($steps);
                }
            }
        } elseif( $event->type == 'quiz' ) {
            $quiz = EventQuiz::where('event_id', $event_id)->first();
            if( $quiz ) {
                $questions = $quiz->questions;
                if( !empty($questions) ) {
                    foreach( $questions as $question ) {
                        EventQuizQuestion::destroy($question->id);
                    }
                }
            }
            EventQuiz::destroy($quiz->id);
        }


        event(new EventDeleted($event, $event->guild));
        Event::destroy($event_id);

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

    public function getGuestableGuilds( Request $request, Guild $guild ) {
        $guilds = Guild::where('active', 1)
            ->where('id', '!=', $guild->id)
            ->get();
        $filtered = $guilds->filter(function ($value, $key) {
            return $value->settings->events_accept_invits == true;
        });
        return response()->json($filtered->all(), 200);
    }

    public function getInvits( Request $request, Guild $guild ) {
        $invits = EventInvit::where('guild_id', $guild->id)
            ->whereHas('event', function($q) {
                $q->where('start_time', '>', date('Y-m-d H:i:s'));
            })
            ->get()->each->setAppends(['guild','event']);
        return response()->json($invits, 200);
    }

    public function acceptInvit(Request $request, Guild $guild, EventInvit $invit ) {
        $invit->accept();
        $invits = EventInvit::where('guild_id', $guild->id)->get()->each->setAppends(['guild','event']);
        return response()->json($invits, 200);
    }

    public function refuseInvit(Request $request, Guild $guild, EventInvit $invit ) {
        $invit->refuse();
        $invits = EventInvit::where('guild_id', $guild->id)->get()->each->setAppends(['guild','event']);
        return response()->json($invits, 200);
    }

    public static function addQuizAnswer( Request $request ) {

        $event = Event::where('channel_discord_id', $request->channel_discord_id)->first();
        if( !$event->quiz ) return response()->json('Cet event ne dispose pas de quiz', 400);

        $args = [
            'answer' => $request->answer,
            'user_discord_id' => $request->user_discord_id,
            'guild_discord_id' => $request->guild_discord_id,
            'message_discord_id' => $request->message_discord_id,
        ];

        $event->quiz->addAnswer($args);
        return response()->json(null, 204);
    }

}
