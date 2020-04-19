<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\City;
use App\Models\Zone;
use App\Models\Stop;
use App\Models\Role;
use App\User;
use Illuminate\Support\Facades\Auth;

class CityController extends Controller {

    public function getAll(Request $request){
        return response()->json($user->getCities(), 200);
    }

    public function getOne(Request $request, City $city){
        return response()->json($city, 200);
    }

    public function getZones(Request $request, City $city) {
        $user = Auth::user();
        /*if( !$user->can('zone_edit', ['city_id' => $city->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }*/
        $zones = Zone::where('city_id', $city->id)
            ->orderBy('name', 'asc')
            ->get();
        return response()->json($zones, 200);
    }

    public function getZone(Request $request, City $city, Zone $zone) {
        $user = Auth::user();
        if( !$user->can('zone_edit', ['city_id' => $city->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
        return response()->json($zone, 200);
    }

    public function createZone(Request $request, City $city) {
        $user = Auth::user();
        if( !$user->can('zone_edit', ['city_id' => $city->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
        $zone = Zone::create([
            'name' => $request->name,
            'city_id' => $city->id,
        ]);
        return response()->json($zone, 200);
    }

    public function saveZone(Request $request, City $city, Zone $zone) {
        $user = Auth::user();
        if( !$user->can('zone_edit', ['city_id' => $city->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
        $zone->update([
            'name' => $request->name,
        ]);
        return response()->json($zone, 200);
    }

    public function deleteZone(Request $request, City $city, Zone $zone) {
        $user = Auth::user();
        if( !$user->can('zone_edit', ['city_id' => $city->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
        Zone::destroy($zone->id);
        return response()->json(null, 204);
    }

    public function getGym(Request $request, City $city, Stop $stop) {
        $user = Auth::user();
        if( !$user->can('poi_edit', ['city_id' => $city->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
        return response()->json($stop, 200);
    }

    public function createGym(Request $request, City $city) {
        $user = Auth::user();
        if( !$user->can('poi_edit', ['city_id' => $city->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
        $gym = Stop::create([
            'name' => $request->name,
            'niantic_name' => $request->niantic_name,
            'description' => $request->description,
            'zone_id' => $request->zone_id,
            'city_id' => $city->id,
            'gym' => $request->gym,
            'ex' => $request->ex,
            'lat' => $request->lat,
            'lng' => $request->lng,
        ]);
        $gym->syncAliases($request->aliases);
        return response()->json($gym, 200);
    }

    public function saveGym(Request $request, City $city, Stop $stop) {
        $user = Auth::user();
        if( !$user->can('poi_edit', ['city_id' => $city->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
        $stop->update([
            'name' => $request->name,
            'niantic_name' => $request->niantic_name,
            'description' => $request->description,
            'zone_id' => $request->zone_id,
            'ex' => ( $request->gym ) ? $request->ex : false,
            'gym' => $request->gym,
            'lat' => $request->lat,
            'lng' => $request->lng,
        ]);
        $stop->syncAliases($request->aliases);

        $roles = Role::where('type', 'gym')
            ->where('gym_id', $stop->id)
            ->get();

        if( $roles ) {
            foreach( $roles as $role ) {
                $role->change([]);
            }
        }

        return response()->json($stop, 200);
    }

    public function deleteGym(Request $request, City $city, Stop $stop) {
        $user = Auth::user();
        if( !$user->can('poi_edit', ['city_id' => $city->id]) ) {
            return response()->json('Vous n\'avez pas les permissions nécessaires', 403);
        }
        $stop->touch();
        Stop::destroy($stop->id);
        return response()->json(null, 204);
    }

    public function getLastChanges( Request $request, City $city ) {
        $date_admin = new \DateTime('2000-01-01');

        $date_events = new \DateTime('2000-01-01');
        $last_event = \DB::table('events')->orderby('updated_at', 'desc')->first();
        if( !empty($last_event) )$date_events = new \DateTime($last_event->updated_at);

        $array_lists = [
            \DB::table('raids')->max('updated_at'),
            \DB::table('quest_instances')->max('updated_at'),
            \DB::table('rocket_invasions')->max('updated_at'),
        ];
        $lists_events = new \DateTime( max($array_lists) );

        $last_changes = [
            'lists' => $lists_events->getTimestamp(),
            'events' => $date_events->getTimestamp(),
            'admin' => $date_admin->getTimestamp(),
        ];
        return response()->json($last_changes, 200);
    }
}
