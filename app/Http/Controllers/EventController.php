<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Event;
use App\Models\Guild;
use App\Core\Helpers;
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
    public static $capability = 'events_manage';
    public static $feature_message = 'La fonctionnalité n\'est pas active';

    public function getActiveEvents(Request $request, City $city)
    {
        $user = Auth::user();

        $user_guild_ids = Helpers::extractIds($user->getGuilds());
        $city_guild_ids = $city->getGuildsIds();
        $matching_ids = array_intersect($user_guild_ids, $city_guild_ids);

        $events = Event::where('end_time', '>', date('Y-m-d H:i:s'))
            ->whereIn('guild_id', $matching_ids)
            ->get();

        $invits = Event::where('end_time', '>', date('Y-m-d H:i:s'))
            ->whereHas('invits', function ($q) use ($matching_ids) {
                $q->where('status', 'accepted')->whereIn('guild_id', $matching_ids);
            })
            ->get();
        $merged = $events->merge($invits);
        return response()->json($merged->all(), 200);
    }

    public function getGuildEvents(Request $request, Guild $guild)
    {
        $user = Auth::user();
        if (!$user->can(self::$capability, ['guild_id' => $guild->id])) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }

        $events = Event::where('guild_id', $guild->id)->get();

        return response()->json($events, 200);
    }

    public function getEvent(Request $request, City $city, Event $event)
    {
        $user = Auth::user();

        $user_guild_ids = Helpers::extractIds($user->getGuilds());
        if (!in_array($event->guild_id, $user_guild_ids)) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }

        return response()->json($event, 200);
    }

    public  function createEvent(Request $request, Guild $guild)
    {
        if (!config(self::$feature)) return response()->json(self::$feature_message, 403);
        $user = Auth::user();
        if (!$user->can(self::$capability, ['guild_id' => $guild->id])) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }

        $args = [
            'event' => [
                'name' => $request->name,
                'guild_id' => $guild->id,
                'city_id' => $guild->city->id,
                'type' => $request->type,
                'start_time' => $request->start_time,
                'image' => $request->image,
                'multi_guilds' => $request->multi_guilds,
                'channel_discord_type' => $request->channel_discord_type,
                'channel_discord_id' => $request->channel_discord_id,
                'category_discord_id' => $request->category_discord_id,
            ],
            'guests' => $request->guests,
            'steps' => $request->steps,
            'quiz' => $request->quiz,
        ];

        $event = Event::add($args);
        return response()->json($event, 200);
    }

    public  function updateEvent(Request $request, Guild $guild, Event $event)
    {
        if (!config(self::$feature)) return response()->json(self::$feature_message, 403);
        $user = Auth::user();
        if (!$user->can('guild_manage', ['guild_id' => $guild->id])) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }

        $args = [
            'event' => [
                'name' => $request->name,
                'type' => $request->type,
                'start_time' => $request->start_time,
                'image' => $request->image,
                'multi_guilds' => $request->multi_guilds,
                'channel_discord_type' => $request->channel_discord_type,
                'channel_discord_id' => $request->channel_discord_id,
                'category_discord_id' => $request->category_discord_id,
            ],
            'guests' => $request->guests,
            'steps' => $request->steps,
            'quiz' => $request->quiz,
        ];

        $event->change($args);

        return response()->json($event, 200);
    }

    public function deleteEvent(Request $request, Guild $guild, Event $event)
    {
        $user = Auth::user();
        if (!$user->can(self::$capability, ['guild_id' => $guild->id])) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }

        event(new EventDeleted($event, $event->guild));
        $event->suppr();

        return response()->json(null, 204);
    }

    public function updateSteps(Request $request, Guild $guild, Event $event)
    {
        $user = Auth::user();
        if (!$user->can('events_train_check', ['guild_id' => $guild->id])) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
        $event->setTrain(['steps' => $request->steps]);
        return response()->json($event, 200);
    }

    public function checkStep(Request $request, Guild $guild, Event $event, EventTrainStep $step)
    {
        $user = Auth::user();
        if (!$user->can('events_train_check', ['guild_id' => $guild->id])) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
        $step->check();
        return response()->json($event, 200);
    }

    public function uncheckStep(Request $request, Guild $guild, Event $event, EventTrainStep $step)
    {
        $user = Auth::user();
        if (!$user->can('events_train_check', ['guild_id' => $guild->id])) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
        $step->uncheck();
        return response()->json($event, 200);
    }

    public function getGuestableGuilds(Request $request, Guild $guild)
    {
        $guilds = Guild::where('active', 1)
            ->where('id', '!=', $guild->id)
            ->get();
        $filtered = $guilds->filter(function ($value, $key) {
            return $value->settings->events_accept_invits == true;
        });
        return response()->json($filtered->all(), 200);
    }

    public function getInvits(Request $request, Guild $guild)
    {
        $user = Auth::user();
        if (!$user->can(self::$capability, ['guild_id' => $guild->id])) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
        $invits = EventInvit::where('guild_id', $guild->id)
            ->whereHas('event', function ($q) {
                $q->where('start_time', '>', date('Y-m-d H:i:s'));
            })
            ->get()->each->setAppends(['guild', 'event']);
        return response()->json($invits, 200);
    }

    public function acceptInvit(Request $request, Guild $guild, EventInvit $invit)
    {
        $user = Auth::user();
        if (!$user->can(self::$capability, ['guild_id' => $guild->id])) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
        $invit->accept();
        $invits = EventInvit::where('guild_id', $guild->id)->get()->each->setAppends(['guild', 'event']);
        return response()->json($invits, 200);
    }

    public function refuseInvit(Request $request, Guild $guild, EventInvit $invit)
    {
        $user = Auth::user();
        if (!$user->can(self::$capability, ['guild_id' => $guild->id])) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
        $invit->refuse();
        $invits = EventInvit::where('guild_id', $guild->id)->get()->each->setAppends(['guild', 'event']);
        return response()->json($invits, 200);
    }

    public function getThemes(Request $request)
    {
        return response()->json(\App\Models\QuizTheme::all(), 200);
    }
}