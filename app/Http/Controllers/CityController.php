<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\models\City;
use App\models\Zone;
use App\models\Stop;
use App\models\Role;
use App\User;

class CityController extends Controller {

    public function getAll(Request $request){
        $user = User::find(1);
        return response()->json($user->getCities(), 200);
    }

    public function getOne(Request $request, City $city){
        $user = User::find(1);
        return response()->json($city, 200);
    }

    public function getZones(Request $request, City $city) {
        $zones = Zone::where('city_id', $city->id)
            ->orderBy('name', 'asc')
            ->get();
        return response()->json($zones, 200);
    }

    public function getZone(Request $request, City $city, Zone $zone) {
        return response()->json($zone, 200);
    }

    public function createZone(Request $request, City $city) {
        $zone = Zone::create([
            'name' => $request->name,
            'city_id' => $city->id,
        ]);
        return response()->json($zone, 200);
    }

    public function saveZone(Request $request, City $city, Zone $zone) {
        $zone->update([
            'name' => $request->name,
        ]);
        return response()->json($zone, 200);
    }

    public function deleteZone(Request $request, City $city, Zone $zone) {
        Zone::destroy($zone->id);
        return response()->json(null, 204);
    }

    public function getGym(Request $request, City $city, Stop $stop) {
        return response()->json($stop, 200);
    }

    public function createGym(Request $request, City $city) {
        $gym = Stop::create([
            'name' => $request->name,
            'niantic_name' => $request->niantic_name,
            'description' => $request->description,
            'zone_id' => $request->zone_id,
            'city_id' => $city->id,
            'ex' => $request->ex,
            'lat' => $request->lat,
            'lng' => $request->lng,
        ]);
        return response()->json($gym, 200);
    }

    public function saveGym(Request $request, City $city, Stop $stop) {
        $stop->update([
            'name' => $request->name,
            'niantic_name' => $request->niantic_name,
            'description' => $request->description,
            'zone_id' => $request->zone_id,
            'ex' => $request->ex,
            'lat' => $request->lat,
            'lng' => $request->lng,

        ]);

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
        Zone::destroy($stop->id);
        return response()->json(null, 204);
    }
}
